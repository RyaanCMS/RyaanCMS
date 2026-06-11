<?php

namespace App\Services\AI;

use App\Models\KbPromptCache;
use Illuminate\Support\Str;

/**
 * KnowledgeBaseService — Central AI cost-reduction engine.
 *
 * Instead of sending 50 000 tokens explaining how a hospital works,
 * we pre-store domain knowledge here and inject a ~800-token context block.
 *
 * Covers:
 *  - RBAC patterns  (roles + permissions per domain)
 *  - Business workflows  (step-by-step process flows)
 *  - Standard reports  (what reports exist for each domain)
 *  - Architecture patterns  (monolith / SaaS / marketplace)
 *  - Integration context  (package names, env keys, quick setup)
 *  - Validation rules  (reusable field-level rules)
 *  - Prompt cache  (DB-backed, 30-day TTL, shared across users)
 */
class KnowledgeBaseService
{
    private IntentEngine $intentEngine;
    private BlueprintGenomeEngine $genomeEngine;

    public function __construct(
        ?IntentEngine $intentEngine = null,
        ?BlueprintGenomeEngine $genomeEngine = null
    ) {
        $this->intentEngine = $intentEngine ?? new IntentEngine();
        $this->genomeEngine = $genomeEngine ?? new BlueprintGenomeEngine();
    }


    // ── RBAC: roles + key permissions per domain ──────────────────────────────
    private const RBAC = [
        'crm'                => ['roles' => ['Super Admin', 'Sales Manager', 'Sales Rep', 'Customer'],
                                  'note'  => 'Sales Reps own their own leads only. Managers see team leads. Admin sees all.'],
        'erp'                => ['roles' => ['Super Admin', 'Finance Manager', 'Procurement Manager', 'Inventory Manager', 'Staff'],
                                  'note'  => 'Department isolation — Procurement cannot edit Finance entries.'],
        'ecommerce'          => ['roles' => ['Super Admin', 'Store Manager', 'Product Manager', 'Customer'],
                                  'note'  => 'Customers see only their own orders. Store Manager manages products and orders.'],
        'marketplace'        => ['roles' => ['Super Admin', 'Platform Admin', 'Vendor', 'Buyer'],
                                  'note'  => 'Vendors manage only their own listings. Platform Admin mediates disputes.'],
        'hrm'                => ['roles' => ['Super Admin', 'HR Manager', 'Department Manager', 'Employee'],
                                  'note'  => 'Employees view own payslips/leaves only. Dept Manager approves own team leaves.'],
        'accounting'         => ['roles' => ['Super Admin', 'Accountant', 'Auditor', 'Staff'],
                                  'note'  => 'Auditors have read-only access. Accountants create/edit transactions.'],
        'lms'                => ['roles' => ['Super Admin', 'Instructor', 'Student'],
                                  'note'  => 'Instructors manage own courses. Students access enrolled courses only.'],
        'booking'            => ['roles' => ['Super Admin', 'Manager', 'Staff', 'Customer'],
                                  'note'  => 'Staff can view/confirm bookings. Customers book and view own appointments.'],
        'hospital'           => ['roles' => ['Super Admin', 'Doctor', 'Nurse', 'Receptionist', 'Patient', 'Lab Technician'],
                                  'note'  => 'Doctors write prescriptions. Nurses update vitals. Receptionists manage appointments.'],
        'restaurant'         => ['roles' => ['Super Admin', 'Manager', 'Waiter', 'Kitchen Staff', 'Cashier'],
                                  'note'  => 'Kitchen Staff sees orders queue only. Waiter takes and updates own tables.'],
        'saas'               => ['roles' => ['Super Admin', 'Tenant Admin', 'Manager', 'User'],
                                  'note'  => 'Multi-tenant: every record scoped by tenant_id. Tenant Admin manages own workspace.'],
        'school'             => ['roles' => ['Super Admin', 'Principal', 'Teacher', 'Student', 'Parent'],
                                  'note'  => 'Teachers manage own class records. Parents view own child data only.'],
        'inventory'          => ['roles' => ['Super Admin', 'Warehouse Manager', 'Stock Keeper', 'Purchaser'],
                                  'note'  => 'Stock Keeper records movements. Manager approves purchase orders.'],
        'real_estate'        => ['roles' => ['Super Admin', 'Branch Manager', 'Agent', 'Customer'],
                                  'note'  => 'Agents manage own listings and leads. Manager sees branch-wide data.'],
        'hotel_hospitality'  => ['roles' => ['Super Admin', 'Hotel Manager', 'Front Desk', 'Housekeeping', 'Guest'],
                                  'note'  => 'Housekeeping updates room status only. Front Desk manages check-in/out.'],
        'gym_fitness'        => ['roles' => ['Super Admin', 'Gym Manager', 'Trainer', 'Member'],
                                  'note'  => 'Trainers manage assigned members only. Members view own profile and attendance.'],
        'pharmacy'           => ['roles' => ['Super Admin', 'Pharmacist', 'Cashier', 'Stock Manager'],
                                  'note'  => 'Cashier can sell; cannot add stock. Pharmacist handles prescription validation.'],
        'logistics'          => ['roles' => ['Super Admin', 'Operations Manager', 'Dispatch Officer', 'Delivery Agent'],
                                  'note'  => 'Delivery Agents see only assigned shipments. Dispatch assigns and tracks.'],
        'legal'              => ['roles' => ['Super Admin', 'Senior Lawyer', 'Associate', 'Paralegal', 'Client'],
                                  'note'  => 'Clients view own case status. Paralegal cannot edit case outcomes.'],
        'ngo_charity'        => ['roles' => ['Super Admin', 'Program Manager', 'Field Officer', 'Donor', 'Volunteer'],
                                  'note'  => 'Donors view own donation history. Field Officers update beneficiary records.'],
        'project_management' => ['roles' => ['Super Admin', 'Project Manager', 'Team Lead', 'Developer', 'Client'],
                                  'note'  => 'Clients view milestones only. Developers update task status only.'],
        'event_management'   => ['roles' => ['Super Admin', 'Event Organizer', 'Staff', 'Attendee'],
                                  'note'  => 'Attendees view/download own tickets. Staff scans/validates tickets.'],
        'travel_agency'      => ['roles' => ['Super Admin', 'Branch Manager', 'Travel Agent', 'Customer'],
                                  'note'  => 'Agents manage own bookings. Customers view/request their own packages.'],
        'car_rental'         => ['roles' => ['Super Admin', 'Fleet Manager', 'Rental Agent', 'Customer'],
                                  'note'  => 'Rental Agents create bookings. Fleet Manager manages vehicles and maintenance.'],
        'laundry'            => ['roles' => ['Super Admin', 'Manager', 'Counter Staff', 'Delivery Staff', 'Customer'],
                                  'note'  => 'Counter Staff creates/updates orders. Delivery marks as delivered.'],
        'construction'       => ['roles' => ['Super Admin', 'Project Manager', 'Site Engineer', 'Contractor', 'Client'],
                                  'note'  => 'Site Engineer updates daily progress. Contractor sees own work orders only.'],
        'insurance'          => ['roles' => ['Super Admin', 'Branch Manager', 'Agent', 'Claims Officer', 'Policyholder'],
                                  'note'  => 'Claims Officer processes claims. Agent sells policies. Policyholder views own.'],
        'freelance_platform' => ['roles' => ['Super Admin', 'Platform Admin', 'Seller', 'Buyer'],
                                  'note'  => 'Sellers manage own gigs. Buyers place orders. Admin mediates disputes.'],
        'news_media'         => ['roles' => ['Super Admin', 'Editor', 'Reporter', 'Subscriber'],
                                  'note'  => 'Reporters submit drafts. Editor publishes/rejects. Subscribers read published.'],
        'telemedicine'       => ['roles' => ['Super Admin', 'Doctor', 'Nurse', 'Receptionist', 'Patient'],
                                  'note'  => 'Doctors conduct consultations and write prescriptions. Patients book and view own.'],
        'property_rental'    => ['roles' => ['Super Admin', 'Property Owner', 'Manager', 'Tenant'],
                                  'note'  => 'Tenants view own lease and payment history. Manager collects rent and handles maintenance.'],
        'default'            => ['roles' => ['Super Admin', 'Manager', 'Staff', 'User'],
                                  'note'  => 'Standard 4-tier RBAC. Admin full access, Manager operational, Staff limited, User self-service.'],
    ];

    // ── Business Workflows ────────────────────────────────────────────────────
    private const WORKFLOWS = [
        'ecommerce'    => 'Browse → Add to Cart → Checkout → Payment → Order Confirmed → Packing → Shipped → Delivered → Review',
        'booking'      => 'Browse Services → Select Time Slot → Provide Details → Payment → Booking Confirmed → Reminder SMS → Appointment → Complete',
        'hospital'     => 'Patient Registration → Schedule Appointment → Doctor Consultation → Diagnosis → Prescription → Lab Tests → Bill Generation → Payment → Discharge',
        'restaurant'   => 'Table/Online Order → Kitchen Display → Preparing → Ready → Served → Bill Generated → Payment → Complete',
        'hrm'          => 'Job Post → Application → Screening → Interview → Offer Letter → Onboarding → Attendance → Monthly Payroll → Performance Review',
        'lms'          => 'Browse Courses → Enroll (Free/Paid) → Watch Lessons → Complete Quizzes → Track Progress → Certificate on 100%',
        'logistics'    => 'Order Received → Pickup Assigned → Picked Up → In Transit → Out for Delivery → Delivered / Failed → Return to Sender',
        'real_estate'  => 'List Property → Inquiry Received → Schedule Viewing → Offer Made → Negotiation → Deal Closed → Commission',
        'hotel_hospitality' => 'Online Booking → Confirmation Email → Check-In → Room Assignment → Housekeeping → Services → Check-Out → Invoice → Review',
        'saas'         => 'Sign Up → Free Trial → Onboarding → Feature Usage → Usage Limit Hit → Upgrade Prompt → Payment → Active Subscription → Renewal',
        'insurance'    => 'Lead → Quote Generated → Policy Issued → Premium Collection → Policy Renewal → Claim Filed → Claim Assessment → Settlement',
        'construction' => 'Project Kickoff → Site Survey → BOQ Prepared → Contractor Assigned → Work Orders → Progress Updates → Quality Check → Handover → Final Invoice',
        'travel_agency'=> 'Inquiry → Package Quotation → Booking Confirmation → Visa Processing → Pre-Departure Briefing → Trip → Return → Feedback',
        'ngo_charity'  => 'Campaign Created → Donor Outreach → Donation Received → Fund Allocation → Program Execution → Beneficiary Report → Impact Report',
        'default'      => 'Create → Review → Approve → Active → Complete / Cancel',
    ];

    // ── Standard Reports per domain ───────────────────────────────────────────
    private const REPORTS = [
        'crm'           => ['Lead Conversion Rate', 'Sales Pipeline Summary', 'Customer Acquisition by Source', 'Activity Log by Rep', 'Monthly Revenue Forecast'],
        'erp'           => ['Profit & Loss Statement', 'Balance Sheet', 'Cash Flow Report', 'Inventory Turnover', 'Purchase Order Summary'],
        'ecommerce'     => ['Daily/Monthly Sales', 'Revenue by Category', 'Top Products', 'Abandoned Cart Rate', 'Customer Retention', 'COD vs Online Split'],
        'marketplace'   => ['GMV Summary', 'Vendor Performance', 'Commission Collected', 'Dispute Rate', 'Top Sellers'],
        'hrm'           => ['Attendance Summary', 'Leave Utilization', 'Monthly Payroll', 'Employee Headcount', 'Turnover Rate'],
        'accounting'    => ['Income Statement', 'Balance Sheet', 'Cash Flow', 'Accounts Receivable Aging', 'Tax Summary'],
        'lms'           => ['Enrollment by Course', 'Completion Rate', 'Quiz Score Distribution', 'Revenue by Course', 'Active Students'],
        'hospital'      => ['Patient Visits by Department', 'Revenue by Service', 'Doctor Consultation Summary', 'OPD/IPD Count', 'Medicine Dispensed'],
        'school'        => ['Student Attendance', 'Exam Results', 'Fee Collection Status', 'Class-wise Performance', 'Teacher Attendance'],
        'inventory'     => ['Stock Valuation', 'Low Stock Alert', 'Stock Movement History', 'Slow-Moving Items', 'Supplier-wise Purchase'],
        'hotel_hospitality' => ['Occupancy Rate', 'Revenue per Available Room (RevPAR)', 'Check-in/Check-out Summary', 'Revenue by Room Type'],
        'logistics'     => ['Delivery Success Rate', 'Average Delivery Time', 'COD Collection Summary', 'Agent Performance', 'Failed Delivery Analysis'],
        'construction'  => ['Project Budget vs Actual', 'Work Order Completion Rate', 'Material Cost Summary', 'Site Attendance', 'Contractor Payments'],
        'insurance'     => ['Premium Collection', 'Claims Ratio', 'Policy Renewal Rate', 'Agent Sales Summary', 'Outstanding Claims'],
        'default'       => ['Summary Dashboard', 'Item Listing Report', 'Status Distribution', 'Monthly Activity', 'User Activity Log'],
    ];

    // ── Architecture Patterns ─────────────────────────────────────────────────
    private const ARCHITECTURES = [
        'saas' => [
            'pattern' => 'Multi-Tenant SaaS',
            'note'    => 'Every model has tenant_id FK. Global scope filters by auth tenant. Separate subdomain or path per tenant. Subscription + billing via Stripe/Cashier.',
        ],
        'marketplace' => [
            'pattern' => 'Two-Sided Marketplace',
            'note'    => 'Vendor + Buyer roles. Commission calculation on every transaction. Escrow pattern: hold funds until delivery confirmed. Rating system for both parties.',
        ],
        'b2b' => [
            'pattern' => 'B2B Enterprise',
            'note'    => 'Organization/Company entity with users belonging to org. Invite-based registration. Org-level settings and billing. API-first with token auth.',
        ],
        'b2c' => [
            'pattern' => 'B2C Consumer App',
            'note'    => 'Public registration with email verification. Social login supported. Profile management. Notification preferences. Guest checkout option.',
        ],
        'internal' => [
            'pattern' => 'Internal Tool',
            'note'    => 'No public registration. Admin-created accounts only. Audit logging on all changes. Export to CSV/PDF. Optional LDAP/SSO integration.',
        ],
        'default' => [
            'pattern' => 'Standard Laravel MVC',
            'note'    => 'Controllers → Services → Models. Resource routes with policies. Gate/Policy for authorization. Queue for background jobs. Events+Listeners for side effects.',
        ],
    ];

    // ── Common Validation Rules ───────────────────────────────────────────────
    private const VALIDATIONS = [
        'phone_bd'     => "regex:/^(?:\\+?88)?01[3-9]\\d{8}$/ — Bangladesh mobile (01XXXXXXXXX)",
        'phone_global' => "regex:/^\\+?[1-9]\\d{7,14}$/ — E.164 international format",
        'nid_bd'       => "digits_between:10,17 — Bangladesh NID (10 or 17 digits)",
        'password'     => "min:8|regex:/[A-Z]/|regex:/[0-9]/ — min 8 chars, 1 uppercase, 1 number",
        'slug'         => "regex:/^[a-z0-9-]+$/ — lowercase alphanumeric + hyphens only",
        'price'        => "numeric|min:0|decimal:0,2 — non-negative decimal, max 2 decimal places",
        'date_future'  => "after_or_equal:today — must not be in the past",
        'date_range'   => "before:end_date / after:start_date — valid date range pair",
        'image'        => "image|max:2048|mimes:jpg,jpeg,png,webp — max 2MB image upload",
        'file'         => "file|max:10240|mimes:pdf,doc,docx,xls,xlsx — max 10MB document",
        'url'          => "url|max:500 — valid URL",
        'email'        => "email:rfc,dns — RFC + DNS check",
        'rating'       => "integer|between:1,5 — 1-5 star rating",
        'percentage'   => "numeric|between:0,100 — valid percentage",
        'amount'       => "numeric|min:0.01 — must be positive amount",
        'lat'          => "numeric|between:-90,90 — valid latitude",
        'lng'          => "numeric|between:-180,180 — valid longitude",
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a compact KB context block to inject into AI prompts.
     * Replaces AI having to "figure out" domain knowledge — ~800 tokens vs free-text explanation.
     */
    public function buildKbContextFor(string $appType, array $integrations = [], string $businessModel = 'default'): string
    {
        $rbac          = $this->getRbac($appType);
        $workflow      = $this->getWorkflow($appType);
        $reports       = $this->getReports($appType);
        $arch          = $this->getArchitecture($businessModel);
        $integBlock    = empty($integrations) ? '' : $this->getIntegrationsBlock($integrations);
        $validations   = $this->getValidations($appType);

        $rbacRoles = implode(', ', $rbac['roles']);
        $reportList = implode(', ', $reports);

        $block = <<<KB
════════════════════════════════════════════
KNOWLEDGE BASE — APPLY EXACTLY
════════════════════════════════════════════
ROLES & PERMISSIONS ({$appType}):
  Roles: {$rbacRoles}
  Note: {$rbac['note']}

BUSINESS WORKFLOW:
  {$workflow}

STANDARD REPORTS TO BUILD:
  {$reportList}

ARCHITECTURE ({$arch['pattern']}):
  {$arch['note']}

VALIDATION RULES (use in Form Requests):
  {$validations}
KB;

        if ($integBlock) {
            $block .= "\n\nINTEGRATIONS:\n{$integBlock}";
        }

        $block .= "\n════════════════════════════════════════════";

        return $block;
    }

    /**
     * Return the RBAC definition for an app type.
     */
    public function getRbac(string $appType): array
    {
        return self::RBAC[$appType] ?? self::RBAC['default'];
    }

    /**
     * Return the business workflow string for an app type.
     */
    public function getWorkflow(string $appType): string
    {
        return self::WORKFLOWS[$appType] ?? self::WORKFLOWS['default'];
    }

    /**
     * Return standard reports list for an app type.
     */
    public function getReports(string $appType): array
    {
        return self::REPORTS[$appType] ?? self::REPORTS['default'];
    }

    /**
     * Return architecture pattern for a business model.
     */
    public function getArchitecture(string $businessModel): array
    {
        return self::ARCHITECTURES[$businessModel] ?? self::ARCHITECTURES['default'];
    }

    /**
     * Return a compact integration setup block for AI injection.
     */
    public function getIntegrationsBlock(array $integrationNames): string
    {
        $allIntegrations = config('integrations', []);
        $lines = [];

        foreach ($integrationNames as $name) {
            $key  = strtolower(str_replace([' ', '-'], '_', $name));
            $data = $allIntegrations[$key] ?? null;

            if (!$data) {
                // Try fuzzy match by trigger_keywords
                foreach ($allIntegrations as $k => $integ) {
                    foreach ($integ['trigger_keywords'] ?? [] as $kw) {
                        if (str_contains(strtolower($name), strtolower($kw))) {
                            $data = $integ;
                            break 2;
                        }
                    }
                }
            }

            if ($data) {
                $pkg = $data['laravel_package'] ? "Package: {$data['laravel_package']} | " : '';
                $envs = implode(', ', $data['env_keys'] ?? []);
                $lines[] = "  [{$data['name']}] {$pkg}Env: {$envs}";
                $lines[] = "  Setup: {$data['quick_setup']}";
            } else {
                $lines[] = "  [{$name}] — search Packagist for a Laravel package, add to .env.";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Return a compact validation hints string for common fields.
     */
    public function getValidations(string $appType): string
    {
        $always = ['price', 'password', 'email', 'image', 'file', 'url'];

        $domainRules = [
            'hospital'     => ['phone_bd', 'date_future', 'date_range'],
            'school'       => ['phone_bd', 'date_future', 'percentage'],
            'hrm'          => ['phone_bd', 'date_future', 'date_range', 'nid_bd'],
            'ecommerce'    => ['price', 'slug', 'rating', 'amount'],
            'marketplace'  => ['price', 'slug', 'rating', 'percentage', 'amount'],
            'real_estate'  => ['price', 'lat', 'lng', 'phone_global', 'url'],
            'logistics'    => ['phone_bd', 'phone_global', 'amount'],
            'accounting'   => ['amount', 'percentage', 'date_range'],
            'booking'      => ['phone_bd', 'phone_global', 'date_future', 'date_range'],
            'lms'          => ['slug', 'url', 'rating', 'price', 'percentage'],
            'property_rental' => ['phone_bd', 'nid_bd', 'date_future', 'amount'],
        ];

        $specific = $domainRules[$appType] ?? ['phone_global', 'date_future', 'amount'];
        $allKeys  = array_unique(array_merge($always, $specific));

        $lines = [];
        foreach ($allKeys as $k) {
            if (isset(self::VALIDATIONS[$k])) {
                $lines[] = "  {$k}: " . self::VALIDATIONS[$k];
            }
        }

        return implode("\n", $lines);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Prompt Cache (DB-backed, 30-day TTL, shared across users)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Check for a cached response for a given normalized prompt + provider.
     * Returns the cached array (deserialized) or null on miss.
     */
    public function checkPromptCache(string $prompt, string $provider = 'default'): ?array
    {
        $hash = $this->cacheHash($prompt, $provider);

        $record = KbPromptCache::where('hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) return null;

        $record->increment('hit_count');

        $decoded = json_decode($record->response, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Save a response to the prompt cache.
     * TTL = 30 days by default.
     */
    public function savePromptCache(
        string $prompt,
        string $provider,
        array  $response,
        int    $tokensSaved = 0,
        int    $ttlDays = 30
    ): void {
        $hash = $this->cacheHash($prompt, $provider);

        KbPromptCache::updateOrCreate(
            ['hash' => $hash],
            [
                'prompt_summary' => Str::limit($prompt, 250),
                'provider'       => $provider,
                'response'       => json_encode($response),
                'tokens_saved'   => $tokensSaved,
                'hit_count'      => 0,
                'expires_at'     => now()->addDays($ttlDays),
            ]
        );
    }

    /**
     * Delete expired cache entries. Call from a scheduled artisan command.
     */
    public function pruneExpiredCache(): int
    {
        return KbPromptCache::where('expires_at', '<', now())->delete();
    }

    /**
     * Return the total tokens saved across all cache hits.
     */
    public function totalTokensSaved(): int
    {
        return (int) KbPromptCache::selectRaw('SUM(tokens_saved * hit_count) as total')->value('total');
    }

    /**
     * Stats summary for the admin dashboard.
     */
    public function cacheStats(): array
    {
        return [
            'total_entries'  => KbPromptCache::count(),
            'active_entries' => KbPromptCache::where('expires_at', '>', now())->count(),
            'total_hits'     => (int) KbPromptCache::sum('hit_count'),
            'tokens_saved'   => $this->totalTokensSaved(),
            'top_cached'     => KbPromptCache::where('expires_at', '>', now())
                                    ->orderByDesc('hit_count')->limit(5)
                                    ->pluck('hit_count', 'prompt_summary')->toArray(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalize and hash a prompt for cache key generation.
     * Strips extra whitespace and lowercases so near-identical prompts hit the same entry.
     */
    private function cacheHash(string $prompt, string $provider): string
    {
        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($prompt)));
        return md5("{$provider}|{$normalized}");
    }

    /**
     * Match a free-text prompt to the closest domain pack key.
     * Genome IntentEngine is tried first (supports multilingual, 500+ industries).
     * Falls back to keyword scanning if genome returns no match.
     */
    public function matchAppType(string $prompt): string
    {
        // ── Genome-first matching ─────────────────────────────────────────
        $genomeIndustry = $this->intentEngine->quickIndustry($prompt);
        if ($genomeIndustry) {
            // Map genome industry ID → domain_packs key (best-effort)
            $domainPackAliases = [
                'ecommerce'    => 'ecommerce',
                'hospital'     => 'hospital',
                'school'       => 'school',
                'restaurant'   => 'restaurant',
                'hotel'        => 'hotel_hospitality',
                'hrm_system'   => 'hrm',
                'accounting'   => 'accounting',
                'logistics'    => 'logistics',
                'microfinance' => 'microfinance',
                'real_estate'  => 'real_estate',
                'crm_saas'     => 'crm',
                'manufacturing'=> 'manufacturing',
                'ngo'          => 'ngo_charity',
                'saas'         => 'saas',
                'marketplace'  => 'marketplace',
                'construction' => 'construction',
            ];
            $packs = config('domain_packs', []);
            $mapped = $domainPackAliases[$genomeIndustry] ?? $genomeIndustry;
            if (isset($packs[$mapped])) return $mapped;
        }

        // ── Fallback: domain_packs keyword scan ───────────────────────────
        $packs   = config('domain_packs', []);
        $lower   = strtolower($prompt);
        $scores  = [];

        foreach ($packs as $key => $pack) {
            $score = 0;
            foreach ($pack['trigger_keywords'] ?? [] as $kw) {
                if (str_contains($lower, strtolower($kw))) {
                    $score += strlen($kw);
                }
            }
            if ($score > 0) $scores[$key] = $score;
        }

        if (empty($scores)) return 'default';
        arsort($scores);
        return array_key_first($scores);
    }

    /**
     * Enrich a prompt with full genome context (zero AI tokens).
     * Returns structured genome data including modules, roles, workflows, KPIs.
     * Use this before calling AI to reduce required token count.
     */
    public function enrichWithGenome(string $prompt): array
    {
        $intent = $this->intentEngine->detect($prompt);
        $genome = $this->genomeEngine->assemble($intent);

        return [
            'intent'       => $intent,
            'genome'       => $genome,
            'context_text' => $genome['prompt_context'] ?? '',
            'modules'      => $genome['modules']      ?? [],
            'roles'        => $genome['roles']        ?? [],
            'workflows'    => $genome['workflows']    ?? [],
            'integrations' => $genome['integrations'] ?? [],
            'kpis'         => $genome['kpis']         ?? [],
        ];
    }

    /**
     * Return the full prompt template for a common app type.
     * Used to seed the AI builder with optimised discovery prompts.
     */
    public function getPromptTemplate(string $appType): ?string
    {
        $templates = [
            'crm'      => 'Build a CRM with lead pipeline, contact management, activity tracking, deal stages, and sales reports. Users: Admin, Sales Manager, Sales Rep.',
            'erp'      => 'Build an ERP system with procurement, inventory, sales, accounting, and HR modules. Multi-department access control.',
            'ecommerce'=> 'Build an eCommerce store with product catalog, shopping cart, order management, coupon system, and payment gateway integration.',
            'hrm'      => 'Build an HRM system with employee profiles, attendance tracking, leave management, payroll calculation, and performance reviews.',
            'lms'      => 'Build an LMS with course management, video lessons, quizzes, enrollment, progress tracking, and certificates.',
            'hospital' => 'Build a Hospital Management System with patient registration, appointment scheduling, doctor management, prescription, lab, billing.',
            'school'   => 'Build a School Management System with student enrollment, class/section management, attendance, exams, grades, and fee collection.',
            'saas'     => 'Build a multi-tenant SaaS platform with subscription plans, team workspaces, usage tracking, and Stripe billing.',
            'marketplace' => 'Build a two-sided marketplace with vendor onboarding, product listings, order management, commission tracking, and review system.',
            'booking'  => 'Build a booking and appointment system with service catalog, time slot management, online payment, SMS reminders.',
        ];

        return $templates[$appType] ?? null;
    }
}
