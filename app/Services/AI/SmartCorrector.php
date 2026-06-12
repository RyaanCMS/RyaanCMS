<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\Project;

/**
 * Handles small, intent-driven prompts without an AI call.
 * Uses domain knowledge from mvp_blueprints and built-in section maps.
 *
 * Intents handled:
 *  - suggest_sections : "add more section relevant with LMS"
 *  - list_built       : "show me what was built", "list files"
 */
class SmartCorrector
{
    // ── Intent detection ──────────────────────────────────────────────────────

    private const INTENT_PATTERNS = [
        'suggest_sections' => [
            'add more section', 'add more page', 'add more feature', 'add more module',
            'suggest section', 'suggest feature', 'suggest page', 'suggest module',
            'more section', 'more page', 'more feature', 'more module',
            'what else', 'what more', 'additional section', 'additional feature',
            'relevant section', 'relevant feature', 'related section', 'related feature',
            'what section', 'what page', 'what feature', 'what module',
            'show section', 'show feature', 'show page',
        ],
        'list_built' => [
            'what did you build', 'what was built', 'what have you built',
            'show files', 'list files', 'show me files',
            'what modules', 'show modules', 'what pages', 'list pages',
            'what was generated', 'what is generated', 'show generated',
        ],
    ];

    // ── Domain keyword → canonical domain ────────────────────────────────────

    private const DOMAIN_KEYWORDS = [
        'lms'          => 'lms',
        'learning'     => 'lms',
        'course'       => 'lms',
        'elearning'    => 'lms',
        'e-learning'   => 'lms',
        'moodle'       => 'lms',
        'school'       => 'school',
        'university'   => 'school',
        'college'      => 'school',
        'education'    => 'school',
        'student'      => 'school',
        'ecommerce'    => 'ecommerce',
        'e-commerce'   => 'ecommerce',
        'shop'         => 'ecommerce',
        'store'        => 'ecommerce',
        'marketplace'  => 'ecommerce',
        'hrm'          => 'hrm',
        'payroll'      => 'hrm',
        'employee'     => 'hrm',
        'hospital'     => 'hospital',
        'clinic'       => 'hospital',
        'medical'      => 'hospital',
        'patient'      => 'hospital',
        'crm'          => 'crm',
        'saas'         => 'saas',
        'inventory'    => 'inventory',
        'warehouse'    => 'inventory',
        'stock'        => 'inventory',
        'restaurant'   => 'restaurant',
        'food'         => 'restaurant',
        'accounting'   => 'accounting',
        'finance'      => 'accounting',
        'pos'          => 'pos',
        'retail'       => 'pos',
        'real estate'  => 'realestate',
        'property'     => 'realestate',
    ];

    // ── Domain → extended sections ────────────────────────────────────────────

    private const DOMAIN_SECTIONS = [
        'lms' => [
            'Core Extensions' => [
                '📚 Course Catalog'        => 'Browse, search and filter courses by category, level and duration',
                '🎓 My Enrollments'        => 'Student dashboard — enrolled courses, completion %, resume points',
                '📝 Quiz Builder'          => 'MCQ, true/false, fill-in-the-blank assessments with auto-grading',
                '🏆 Certificate Manager'   => 'Auto-issue completion certificates with QR verification',
                '📊 Progress Tracker'      => 'Visual progress bars, time-spent analytics, milestone badges',
                '👨‍🏫 Instructor Dashboard'  => 'Course creation, student roster, grading center, revenue view',
            ],
            'Advanced Modules' => [
                '💬 Discussion Forums'      => 'Per-lesson threaded discussions with upvotes and instructor replies',
                '📹 Video Lessons'          => 'Upload or embed YouTube/Vimeo with watch-time tracking and notes',
                '📁 Assignment Submissions' => 'File-upload tasks with rubric-based grading and feedback',
                '🔔 Smart Notifications'    => 'New lesson, due date, quiz result alerts — email + in-app',
                '💳 Course Payments'        => 'One-time purchase, subscription plans, coupon/discount codes',
                '🌐 Multi-language'         => 'Translate course content, UI labels and certificates',
                '🤝 Cohort Learning'        => 'Time-gated course release for groups starting together',
                '⭐ Reviews & Ratings'      => 'Student reviews per course with instructor response',
                '📈 Analytics Dashboard'    => 'Enrollment trends, completion rates, revenue per course',
                '🏅 Gamification'           => 'XP points, leaderboards, streak rewards for learners',
            ],
        ],

        'school' => [
            'Core Extensions' => [
                '📚 Library Management'  => 'Book inventory, issue/return tracking, overdue fines',
                '🚌 Transport Management'=> 'Route planning, vehicle tracking, student bus passes',
                '🏠 Hostel Management'   => 'Room allocation, mess billing, warden assignments',
                '💻 Online Classes'      => 'Live class links, recording archive, attendance marking',
                '📱 Parent Portal'       => 'Real-time attendance, results, fee status for parents',
                '📝 Online Exams'        => 'MCQ-based exams with auto-grading and SMS result delivery',
            ],
            'Advanced Modules' => [
                '🎨 Extra-curricular'   => 'Clubs, events, achievement tracking',
                '📊 School Analytics'   => 'Pass rates, attendance trends, fee collection status',
                '🤝 Alumni Network'     => 'Graduate directory and event management',
                '📋 Notice Board'       => 'School announcements with category and audience targeting',
            ],
        ],

        'ecommerce' => [
            'Core Extensions' => [
                '⭐ Product Reviews'      => 'Star ratings and written reviews with photo uploads',
                '❤️  Wishlist'            => 'Save products for later, share wishlists',
                '🎁 Gift Cards'           => 'Issue and redeem digital gift cards',
                '🏷️ Coupons & Discounts'  => 'Percentage, fixed, free-shipping codes with usage limits',
                '📦 Shipment Tracking'    => 'Real-time order tracking with carrier integration',
                '💬 Live Chat Support'    => 'Customer chat widget with agent assignment',
            ],
            'Advanced Modules' => [
                '🔄 Subscription Products' => 'Recurring orders with flexible billing intervals',
                '🤖 AI Recommendations'    => '"You may also like" engine based on purchase history',
                '💰 Loyalty Points'        => 'Earn and redeem points on every purchase',
                '🌍 Multi-currency'        => 'Auto-convert prices by visitor country',
                '📊 Sales Analytics'       => 'Revenue trends, top products, cart abandonment',
                '🛒 Abandoned Cart'        => 'Auto-email reminders for abandoned carts',
            ],
        ],

        'hrm' => [
            'Core Extensions' => [
                '📋 Recruitment Module'   => 'Job postings, applicant tracking, interview scheduling',
                '🎯 Performance Reviews'  => '360° appraisals, KPI tracking, goal setting',
                '📚 Training & Learning'  => 'Course assignments, completion tracking, skill matrix',
                '🏢 Asset Management'     => 'Assign devices and equipment to employees',
                '📅 Shift Scheduling'     => 'Roster planner with conflict detection and swap requests',
                '🤝 Onboarding Workflow'  => 'Checklist-driven new-hire onboarding process',
            ],
            'Advanced Modules' => [
                '📊 HR Analytics'          => 'Turnover rate, headcount trends, cost-per-hire',
                '💼 Document Management'   => 'Contract storage, expiry alerts, e-signature',
                '🏅 Awards & Recognition'  => 'Peer nominations, monthly awards, points',
                '🧠 Succession Planning'   => 'Identify high-potential employees for key roles',
            ],
        ],

        'hospital' => [
            'Core Extensions' => [
                '🔬 Lab Module'         => 'Test orders, results entry, printable reports',
                '💊 Pharmacy'           => 'Drug inventory, prescription fulfillment, expiry alerts',
                '📷 Radiology'          => 'X-ray/MRI order tracking, report upload',
                '🛏️ Ward Management'    => 'Bed allocation, admission, discharge, transfer workflows',
                '🩺 Telemedicine'       => 'Video consultations with online prescriptions',
                '🔗 Insurance Claims'   => 'Pre-auth requests, claim submission, settlement tracking',
            ],
            'Advanced Modules' => [
                '🏥 OT Scheduling'      => 'Operation theater booking and surgical team roster',
                '📊 Hospital Analytics' => 'Bed occupancy, revenue per department, doctor utilization',
                '🧬 EMR Timeline'       => 'Full patient history in chronological view',
                '📱 Patient App'        => 'Patient-facing app for appointments and reports',
            ],
        ],

        'crm' => [
            'Core Extensions' => [
                '📧 Email Integration'   => 'Two-way email sync, templates, open/click tracking',
                '📞 Call Logging'        => 'Log calls, record notes, set follow-up reminders',
                '🤖 Lead Scoring'        => 'Auto-score leads based on behavior and profile data',
                '📊 Sales Forecasting'   => 'Pipeline value projection by month and rep',
                '📋 Task Automation'     => 'Auto-assign tasks on deal stage changes',
                '💬 Live Chat'           => 'Website chat widget with lead capture',
            ],
            'Advanced Modules' => [
                '📱 Mobile App'          => 'iOS/Android for on-the-go sales teams',
                '🔗 API Integrations'    => 'Zapier, Slack, WhatsApp Business webhooks',
                '🗓️ Meeting Scheduler'   => 'Calendly-style booking linked to CRM contacts',
                '📈 Territory Management'=> 'Assign leads by region, quota tracking per territory',
            ],
        ],

        'saas' => [
            'Core Extensions' => [
                '🔑 SSO / OAuth'        => 'Google, GitHub, Microsoft single sign-on',
                '📊 Usage Analytics'    => 'Feature adoption, active users, session tracking per tenant',
                '🎨 White-label'        => 'Custom domain, logo, color scheme per tenant',
                '💌 Email Sequences'    => 'Onboarding drip emails, feature announcement campaigns',
                '🔗 Public API'         => 'REST API with key management for tenant developers',
            ],
            'Advanced Modules' => [
                '🤝 Affiliate System'   => 'Referral links, commission tracking, payout management',
                '📋 Audit Logs'         => 'Per-tenant activity history for compliance',
                '⚠️ Status Page'        => 'Uptime history and incident announcements',
                '🧪 Feature Flags'      => 'A/B test features per tenant or plan tier',
            ],
        ],

        'inventory' => [
            'Core Extensions' => [
                '📦 Purchase Orders'    => 'Raise POs, approval workflow, delivery tracking',
                '🏭 Multi-warehouse'    => 'Stock levels per location with transfer requests',
                '📊 Stock Valuation'    => 'FIFO/LIFO/average cost reporting',
                '🔔 Reorder Alerts'     => 'Auto-alert when stock falls below minimum level',
                '📋 Barcode Scanning'   => 'Scan-based stock-in, stock-out, and counting',
            ],
            'Advanced Modules' => [
                '🔗 Supplier Portal'    => 'Supplier login to view POs and update delivery status',
                '📈 Demand Forecasting' => 'Reorder quantity suggestions based on sales history',
                '🔄 Batch/Lot Tracking' => 'Trace batches from receipt to sale for recall management',
            ],
        ],

        'restaurant' => [
            'Core Extensions' => [
                '📲 Online Ordering'    => 'Customer-facing menu, cart and checkout',
                '🛵 Delivery Tracking'  => 'Real-time rider location for delivery orders',
                '⭐ Customer Loyalty'   => 'Points, stamps and rewards for repeat customers',
                '📅 Reservations'       => 'Table booking with confirmation SMS/email',
                '💳 Split Billing'      => 'Split a table bill across multiple payment methods',
            ],
            'Advanced Modules' => [
                '🍽️ Recipe Costing'     => 'Ingredient cost per dish, margin tracking',
                '🔗 Food Aggregators'   => 'Sync menu and orders with Uber Eats, Foodpanda',
                '📊 Sales Reports'      => 'Daily revenue, top dishes, peak hour analysis',
                '📱 Self-order Kiosk'   => 'Touchscreen ordering at the table or counter',
            ],
        ],

        'accounting' => [
            'Core Extensions' => [
                '📊 Financial Statements' => 'P&L, Balance Sheet, Cash Flow auto-generated',
                '🧾 Expense Claims'       => 'Staff expense submission with receipt and approval',
                '🏦 Bank Reconciliation'  => 'Match bank statement lines to journal entries',
                '📅 Budgeting'            => 'Annual budget vs actual variance tracking',
                '💰 Multi-currency'       => 'Foreign exchange transactions with rate snapshots',
            ],
            'Advanced Modules' => [
                '🔗 Tax Filing'    => 'VAT/GST return preparation and e-filing integration',
                '📋 Audit Trail'   => 'Immutable log of every financial entry change',
                '📈 CFO Dashboard' => 'Cash runway, burn rate, AR/AP aging in one view',
            ],
        ],

        'realestate' => [
            'Core Extensions' => [
                '🏠 Property Listings'    => 'Searchable catalog with photos, floor plans, map view',
                '📅 Viewing Appointments' => 'Schedule and track property viewings',
                '💰 Offer Management'     => 'Record and negotiate buyer offers',
                '📋 Tenancy Contracts'    => 'Digital lease generation, e-signature, renewal alerts',
                '💳 Rent Collection'      => 'Recurring rent invoicing, payment tracking, receipts',
            ],
            'Advanced Modules' => [
                '🔧 Maintenance Requests' => 'Tenant fault reporting with contractor assignment',
                '📊 Portfolio Analytics'  => 'Yield, occupancy rate, expense tracking per property',
                '🤖 AI Valuation'         => 'Automated property price estimate from comparables',
            ],
        ],

        'pos' => [
            'Core Extensions' => [
                '🖥️ Touch POS Screen'    => 'Fast touchscreen order entry with category shortcuts',
                '💳 Multiple Payment'    => 'Cash, card, mobile money, split payment in one sale',
                '🔄 Returns & Refunds'   => 'Easy return workflow with inventory restock',
                '📦 Stock Alerts'        => 'Low-stock notification at point of sale',
                '🧾 Receipt Printing'    => 'Thermal printer integration with custom receipt template',
            ],
            'Advanced Modules' => [
                '⭐ Customer Display'   => 'Second screen showing items and total to the customer',
                '📊 POS Analytics'      => 'Hourly sales, cashier performance, product mix',
                '🔗 E-commerce Sync'    => 'Unified stock between physical and online store',
            ],
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function canHandle(string $prompt, Project $project): bool
    {
        $lower = strtolower(trim($prompt));

        // Only intercept short prompts — longer ones contain enough context for AI
        if (str_word_count($lower) > 15) return false;

        $intent = $this->detectIntent($lower);
        if (!$intent) return false;

        // list_built works without domain knowledge
        if ($intent === 'list_built') return true;

        // suggest_sections needs a known domain with data
        $domain = $this->detectDomain($lower, $project);
        return $domain !== null && isset(self::DOMAIN_SECTIONS[$domain]);
    }

    public function handle(
        string          $prompt,
        Project         $project,
        AIConversation  $conversation,
        callable        $emit
    ): void {
        $lower  = strtolower(trim($prompt));
        $intent = $this->detectIntent($lower);
        $domain = $this->detectDomain($lower, $project);

        // Record user message in conversation
        $conversation->addMessage('user', $prompt, []);

        $response = match ($intent) {
            'suggest_sections' => $this->buildSuggestResponse($domain),
            'list_built'       => $this->buildListBuiltResponse($project),
            default            => null,
        };

        if (!$response) {
            $emit(['type' => 'error', 'message' => 'Smart KB could not answer this.']);
            return;
        }

        // Stream the response word by word for a natural feel
        $words = explode(' ', $response);
        $buffer = '';
        foreach ($words as $i => $word) {
            $buffer .= ($i === 0 ? '' : ' ') . $word;
            if (($i + 1) % 8 === 0) {
                $emit(['type' => 'chunk', 'content' => $buffer]);
                $buffer = '';
            }
        }
        if ($buffer !== '') {
            $emit(['type' => 'chunk', 'content' => $buffer]);
        }

        // Record assistant response in conversation
        $conversation->addMessage('assistant', $response, [
            'tokens_used' => 0,
            'model'       => 'smart-kb',
        ]);

        $emit([
            'type'        => 'done',
            'message'     => $response,
            'model'       => 'smart-kb',
            'files'       => [],
            'tokens_used' => 0,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function detectIntent(string $lower): ?string
    {
        foreach (self::INTENT_PATTERNS as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($lower, $pattern)) {
                    return $intent;
                }
            }
        }
        return null;
    }

    private function detectDomain(string $lower, Project $project): ?string
    {
        // 1. Project blueprint takes priority
        $blueprint = $project->blueprint ?? [];
        $appType   = $blueprint['app_type'] ?? $blueprint['domain'] ?? null;
        if ($appType && isset(self::DOMAIN_SECTIONS[$appType])) {
            return $appType;
        }

        // Map blueprint app_type through keyword table if not a direct key
        if ($appType) {
            $mapped = self::DOMAIN_KEYWORDS[strtolower($appType)] ?? null;
            if ($mapped && isset(self::DOMAIN_SECTIONS[$mapped])) return $mapped;
        }

        // 2. Prompt keyword scan
        foreach (self::DOMAIN_KEYWORDS as $keyword => $domain) {
            if (str_contains($lower, $keyword) && isset(self::DOMAIN_SECTIONS[$domain])) {
                return $domain;
            }
        }

        return null;
    }

    private function buildSuggestResponse(?string $domain): ?string
    {
        if (!$domain || !isset(self::DOMAIN_SECTIONS[$domain])) return null;

        $sections = self::DOMAIN_SECTIONS[$domain];
        $label    = strtoupper($domain === 'lms' ? 'LMS' : ucfirst($domain));
        $lines    = ["Here are additional sections you can add to your **{$label}** project:\n"];

        foreach ($sections as $groupName => $items) {
            $lines[] = "\n### {$groupName}";
            foreach ($items as $title => $desc) {
                $lines[] = "\n**{$title}**\n{$desc}";
            }
        }

        $lines[] = "\n\n---\nType **\"build [section name]\"** to add any of these to your project.";

        return implode("\n", $lines);
    }

    private function buildListBuiltResponse(Project $project): string
    {
        $files = $project->files()->orderBy('path')->get(['path']);

        if ($files->isEmpty()) {
            return "No files generated yet. Describe what you want to build and I'll get started!";
        }

        $grouped = $files->groupBy(fn($f) => (explode('/', $f->path)[0] ?? 'root'));
        $lines   = ["Here's what's been built in your project (**{$files->count()} files**):\n"];

        foreach ($grouped as $dir => $group) {
            $lines[] = "\n**{$dir}/** ({$group->count()} files)";
            foreach ($group as $file) {
                $lines[] = "  - `{$file->path}`";
            }
        }

        return implode("\n", $lines);
    }
}
