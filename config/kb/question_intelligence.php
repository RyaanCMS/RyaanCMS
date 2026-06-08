<?php
/**
 * Question Intelligence Knowledge Base
 * What questions should AI ask to clarify requirements before building?
 * "A senior dev always asks the right questions before writing a single line."
 * Level 4: Product Knowledge — prevents wrong features, saves rework
 */
return [

    // ── Question Priority Rule ────────────────────────────────────────────────
    'rule' => 'Ask max 5 questions before building. Focus on: scale, audience, payment, multi-tenancy, existing system.',

    // ── Questions Per Domain ──────────────────────────────────────────────────
    'by_domain' => [

        'ecommerce' => [
            [
                'q'    => 'What country/market is this for?',
                'why'  => 'Determines payment gateway (bKash=BD, Razorpay=IN, Stripe=US), COD requirement, tax system, language',
                'critical' => true,
            ],
            [
                'q'    => 'Will there be multiple vendors or single store?',
                'why'  => 'Multi-vendor requires vendor table, commission engine, split orders — fundamentally different schema',
                'critical' => true,
            ],
            [
                'q'    => 'Do products have variants? (size, color, etc.)',
                'why'  => 'Product variants = product_variants table, variant-level stock tracking',
                'critical' => true,
            ],
            [
                'q'    => 'Is Cash on Delivery required?',
                'why'  => 'COD needs separate order flow, delivery confirmation before payment recording',
            ],
            [
                'q'    => 'Does inventory need to be tracked per warehouse?',
                'why'  => 'Multi-warehouse = more complex stock movement system',
            ],
            [
                'q'    => 'Will there be a discount/coupon system?',
                'why'  => 'Coupons need usage tracking, conditions (first order, min amount, per user)',
            ],
        ],

        'crm' => [
            [
                'q'    => 'Is this for a single company or multiple companies (SaaS)?',
                'why'  => 'Multi-tenant CRM = completely different architecture (tenancy)',
                'critical' => true,
            ],
            [
                'q'    => 'What does your sales pipeline look like? (stages?)',
                'why'  => 'Determines pipeline_stages data, deal fields, workflow automation',
                'critical' => true,
            ],
            [
                'q'    => 'Do you need email/calendar integration?',
                'why'  => 'Gmail/Outlook API adds significant scope — OAuth, token refresh, sync jobs',
            ],
            [
                'q'    => 'How many sales reps / users expected?',
                'why'  => 'Scale and permission model: team hierarchy, assignment rules',
            ],
        ],

        'hrm' => [
            [
                'q'    => 'Is this for one company or a SaaS product?',
                'why'  => 'Multi-company HRM = multi-tenancy with separate payroll rules per company',
                'critical' => true,
            ],
            [
                'q'    => 'Does payroll need to be integrated with attendance/leave?',
                'why'  => 'Integration requires: attendance data lock before payroll run, leave deduction rules',
                'critical' => true,
            ],
            [
                'q'    => 'What country tax rules apply? (Bangladesh PAYE, India TDS, etc.)',
                'why'  => 'Tax slabs are country-specific — need to store slab configs per fiscal year',
            ],
            [
                'q'    => 'Do you need biometric/fingerprint device integration?',
                'why'  => 'Biometric integration = device API/SDK, background sync, ZKTeco common in BD',
            ],
            [
                'q'    => 'Is recruitment module needed from day one?',
                'why'  => 'Recruitment adds 15+ tables and a full workflow — significant scope increase',
            ],
        ],

        'hospital' => [
            [
                'q'    => 'Is this single branch or multi-branch hospital?',
                'why'  => 'Multi-branch = branch_id on all tables, cross-branch patient lookup, per-branch billing',
                'critical' => true,
            ],
            [
                'q'    => 'Do you need a pharmacy module?',
                'why'  => 'Pharmacy = medicine stock, prescription dispensing, expiry tracking — separate complex module',
                'critical' => true,
            ],
            [
                'q'    => 'Is lab (pathology) module needed?',
                'why'  => 'Lab = test categories, sample collection, result entry, report generation',
            ],
            [
                'q'    => 'Does billing need to handle insurance claims?',
                'why'  => 'Insurance adds claim submission, approval workflow, partial payment, rejection handling',
            ],
            [
                'q'    => 'Is telemedicine (video consultation) needed?',
                'why'  => 'Video = WebRTC/Agora/Twilio Video SDK — major additional scope',
            ],
        ],

        'saas' => [
            [
                'q'    => 'What is the core feature that makes this SaaS valuable?',
                'why'  => 'Core feature defines domain tables and API — everything else is SaaS infrastructure',
                'critical' => true,
            ],
            [
                'q'    => 'How will tenants be isolated? (subdomain, separate DB, or row-level?)',
                'why'  => 'Tenancy strategy is architectural — hard to change later',
                'critical' => true,
            ],
            [
                'q'    => 'What are the pricing plans and limits?',
                'why'  => 'Plan limits = feature_flags/limits in plans table, enforced at service layer',
                'critical' => true,
            ],
            [
                'q'    => 'Do tenants need team members with different roles?',
                'why'  => 'Team + RBAC within tenant = team_members table, role_user pivot per tenant',
            ],
            [
                'q'    => 'Which payment gateway and billing model? (monthly/annual/usage-based)',
                'why'  => 'Billing model affects subscription lifecycle design (proration, trials, overage)',
            ],
        ],

        'school' => [
            [
                'q'    => 'Does the system need a parent portal?',
                'why'  => 'Parent portal = parent role with limited view of their child\'s data only',
            ],
            [
                'q'    => 'Is online fee payment required?',
                'why'  => 'Online fees = payment gateway integration, receipt generation',
            ],
            [
                'q'    => 'Do you need online exam / assignment submission?',
                'why'  => 'Online assignments = file upload, submission tracking, online marking',
            ],
            [
                'q'    => 'Single school or multi-school (franchise) system?',
                'why'  => 'Multi-school = multi-tenancy with per-school config and separate data',
                'critical' => true,
            ],
        ],

        'restaurant' => [
            [
                'q'    => 'Dine-in only or also delivery / takeaway?',
                'why'  => 'Delivery = delivery person assignment, tracking, status updates, estimated time',
                'critical' => true,
            ],
            [
                'q'    => 'Do you need online ordering (customer app/website)?',
                'why'  => 'Online ordering = customer-facing menu, cart, checkout, payment — entire separate UI',
                'critical' => true,
            ],
            [
                'q'    => 'Do you have multiple branches?',
                'why'  => 'Multi-branch = branch_id everywhere, per-branch menu, per-branch reports',
            ],
            [
                'q'    => 'Is table reservation needed?',
                'why'  => 'Reservation = time-slot availability checking, conflict prevention, reminder notifications',
            ],
        ],

        'inventory' => [
            [
                'q'    => 'Is this single or multi-warehouse?',
                'why'  => 'Multi-warehouse = transfers between warehouses, per-warehouse stock tracking',
                'critical' => true,
            ],
            [
                'q'    => 'Do products have expiry dates? (pharmacy, food, cosmetics)',
                'why'  => 'Expiry tracking = batch-level stock, FEFO picking, expiry alerts',
            ],
            [
                'q'    => 'Is this linked to sales/purchases or standalone inventory?',
                'why'  => 'Linked to sales = stock auto-decrements on sale, stock alerts trigger purchase orders',
            ],
            [
                'q'    => 'Do you need barcode/QR scanning?',
                'why'  => 'Barcode = camera access, barcode generation on product create, mobile-friendly POS UI',
            ],
        ],

        'marketplace' => [
            [
                'q'    => 'Physical products, digital products, or services?',
                'why'  => 'Digital = instant delivery, no shipping; services = booking/scheduling flow',
                'critical' => true,
            ],
            [
                'q'    => 'How is vendor payment handled? (commission deducted or separate payout?)',
                'why'  => 'Commission model defines: when money is moved, how disputes affect payouts',
                'critical' => true,
            ],
            [
                'q'    => 'Do vendors manage their own shipping or does platform handle it?',
                'why'  => 'Vendor shipping = per-vendor delivery rules; platform = single logistics integration',
            ],
            [
                'q'    => 'Is there a dispute/refund process for buyers?',
                'why'  => 'Disputes = dispute table, mediation workflow, hold payout until resolved',
            ],
        ],

        'api' => [
            [
                'q'    => 'Who are the API consumers? (mobile app, third-party, public?)',
                'why'  => 'Determines auth method (Sanctum for mobile, OAuth for third-party, API key for public)',
                'critical' => true,
            ],
            [
                'q'    => 'Do API consumers need different permission levels?',
                'why'  => 'Scopes/abilities on tokens — what each app can read or write',
            ],
            [
                'q'    => 'Is rate limiting required?',
                'why'  => 'Rate limiting per IP, per user, per API key — protects against abuse',
            ],
        ],

        'general' => [
            [
                'q'    => 'What is the expected user count at launch? At 1 year?',
                'why'  => 'Determines scale tier: shared hosting vs VPS vs cloud, caching strategy',
                'critical' => true,
            ],
            [
                'q'    => 'Are there any existing systems this needs to integrate with?',
                'why'  => 'Legacy integrations = API design, data migration, ETL jobs',
            ],
            [
                'q'    => 'Is mobile app (Android/iOS) needed alongside web?',
                'why'  => 'Mobile app = API-first design mandatory, token auth, different file upload handling',
            ],
        ],
    ],

    // ── Scope-Triggered Questions ─────────────────────────────────────────────
    'triggered_by_scope' => [
        'if_saas'        => 'What country is your target market? What are your plan limits?',
        'if_financial'   => 'What currency? Which payment gateway? Is tax calculation needed?',
        'if_multi_role'  => 'Who are the different user types and what can each role do?',
        'if_reports'     => 'Who needs reports? What decisions will they make from them?',
        'if_realtime'    => 'What needs to be real-time? (notifications, live updates, chat?)',
    ],

];
