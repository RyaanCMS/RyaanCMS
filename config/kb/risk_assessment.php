<?php
/**
 * Risk Assessment Knowledge Base
 * Technical and business risk scoring for project decisions.
 * AI uses this to flag dangerous choices and suggest mitigations.
 * Level 6: Experience Knowledge — "Avoid the mistakes senior devs have seen kill projects."
 */
return [

    // ── Risk Scale ───────────────────────────────────────────────────────────
    'scale' => ['1 = Negligible', '2 = Low', '3 = Medium', '4 = High', '5 = Critical — must address'],

    // ── Technical Risks ────────────────────────────────────────────────────────
    'technical' => [

        'no_test_coverage' => [
            'risk'      => 4,
            'trigger'   => 'No automated tests mentioned',
            'impact'    => 'Regressions ship undetected — growing codebase becomes unmaintainable',
            'mitigation'=> 'Minimum: feature tests for auth, permissions, core business flows',
        ],

        'no_queue_for_email' => [
            'risk'      => 4,
            'trigger'   => 'Email sent in request lifecycle',
            'impact'    => 'Slow page responses, timeouts, failed emails not retried',
            'mitigation'=> 'Queue ALL email via notification job — never Mail::send() in controller',
        ],

        'no_pagination' => [
            'risk'      => 5,
            'trigger'   => 'Model::all() or Model::get() without limit on tables > 1K rows',
            'impact'    => 'Memory exhaustion, slow responses, server crash under load',
            'mitigation'=> 'Always paginate(25) — NEVER ::all() except in migrations/seeders',
        ],

        'missing_transactions' => [
            'risk'      => 5,
            'trigger'   => 'Multi-table write without DB::transaction()',
            'impact'    => 'Data corruption on partial failure — order created but stock not decremented',
            'mitigation'=> 'Wrap every multi-step DB write in DB::transaction() with rollback',
        ],

        'no_authorization_checks' => [
            'risk'      => 5,
            'trigger'   => 'Route accessible without policy/gate check',
            'impact'    => 'IDOR — users access other users\' data (critical security vulnerability)',
            'mitigation'=> 'Every route that touches user data MUST have $this->authorize() or policy check',
        ],

        'storing_secrets_in_code' => [
            'risk'      => 5,
            'trigger'   => 'API keys, passwords, tokens in source code or config files',
            'impact'    => 'Credentials exposed in git history — attacker accesses all connected services',
            'mitigation'=> 'ALL secrets in .env only — never commit .env — use .env.example with placeholders',
        ],

        'n_plus_1_queries' => [
            'risk'      => 4,
            'trigger'   => 'Looping over a collection and accessing relationships inside the loop',
            'impact'    => '100 records = 101 queries — performance degrades with scale',
            'mitigation'=> 'Always with() — enable Telescope/Debugbar to catch N+1 in development',
        ],

        'no_file_upload_validation' => [
            'risk'      => 5,
            'trigger'   => 'Accepting file uploads without mime/size/extension validation',
            'impact'    => 'Remote code execution via malicious file upload',
            'mitigation'=> 'Validate mime type (not just extension), max size, store outside public, signed URL for access',
        ],

        'missing_rate_limiting' => [
            'risk'      => 4,
            'trigger'   => 'Login, OTP, password reset endpoints without throttle middleware',
            'impact'    => 'Brute force attacks, credential stuffing, SMS flooding costs',
            'mitigation'=> 'throttle:5,1 on auth endpoints, throttle:60,1 on API endpoints',
        ],

        'no_database_indexes' => [
            'risk'      => 4,
            'trigger'   => 'Queries on unindexed FK or WHERE columns',
            'impact'    => 'Full table scans — queries that take ms at 1K rows take seconds at 100K rows',
            'mitigation'=> 'Index every FK column, every column used in WHERE/ORDER BY/GROUP BY',
        ],

        'synchronous_payment_confirmation' => [
            'risk'      => 5,
            'trigger'   => 'Trusting payment redirect URL for order confirmation',
            'impact'    => 'Attackers manipulate redirect parameters to fake successful payment',
            'mitigation'=> 'ONLY trust payment gateway webhook with signature verification',
        ],

        'no_soft_delete' => [
            'risk'      => 3,
            'trigger'   => 'Hard-deleting important records (users, orders, invoices)',
            'impact'    => 'Data loss — deleted records referenced in reports, audit logs, foreign keys',
            'mitigation'=> 'Use SoftDeletes on all business-critical models — hard delete only after retention period',
        ],

        'massive_monolith_from_start' => [
            'risk'      => 3,
            'trigger'   => 'Building all modules at once for MVP',
            'impact'    => 'Delayed launch, scope creep, premature optimization, wasted features',
            'mitigation'=> 'Ship core loop first — expand based on real user feedback',
        ],

        'no_backup_strategy' => [
            'risk'      => 5,
            'trigger'   => 'Production database without automated backups',
            'impact'    => 'Any data corruption or accidental deletion = total data loss',
            'mitigation'=> 'spatie/laravel-backup daily to S3 — test restore quarterly',
        ],

        'plain_text_passwords_possible' => [
            'risk'      => 5,
            'trigger'   => 'Password stored without bcrypt hash',
            'impact'    => 'All user passwords exposed in any data breach',
            'mitigation'=> 'Always Hash::make() — never store plain, md5, sha1 passwords',
        ],
    ],

    // ── Business / Product Risks ───────────────────────────────────────────────
    'business' => [

        'no_payment_backup' => [
            'risk'      => 4,
            'trigger'   => 'Single payment gateway, no fallback',
            'impact'    => 'Gateway downtime = zero revenue for that period',
            'mitigation'=> 'At minimum: primary + fallback gateway selector',
        ],

        'building_wrong_feature' => [
            'risk'      => 4,
            'trigger'   => 'Building complex features before validating core use case',
            'impact'    => 'Wasted months — users want something different',
            'mitigation'=> 'Ship simplest version → measure → then build next feature',
        ],

        'no_data_export_for_users' => [
            'risk'      => 3,
            'trigger'   => 'SaaS without data export option for users',
            'impact'    => 'Vendor lock-in concern — major friction for enterprise buyers',
            'mitigation'=> 'Allow CSV/Excel export of user\'s own data — required for GDPR',
        ],

        'manual_ops_at_scale' => [
            'risk'      => 3,
            'trigger'   => 'Processes that work manually at 100 users but break at 10K',
            'impact'    => 'Business-limiting bottleneck — support overwhelmed, data integrity issues',
            'mitigation'=> 'Identify and automate: invoicing, subscription renewal, low stock alerts',
        ],

        'no_monitoring_alerting' => [
            'risk'      => 4,
            'trigger'   => 'Production system with no error tracking or alerting',
            'impact'    => 'Unknown failures — customers leave before you notice the problem',
            'mitigation'=> 'Minimum: Sentry for error tracking, UptimeRobot for availability, queue failure notifications',
        ],

        'single_admin_account' => [
            'risk'      => 3,
            'trigger'   => 'System relies on single super-admin account',
            'impact'    => 'If that account is compromised or person leaves — loss of control',
            'mitigation'=> 'Multiple admins, 2FA on all admin accounts, audit log on admin actions',
        ],
    ],

    // ── Risk Score Calculator ─────────────────────────────────────────────────
    'assessment_logic' => [
        'auto_flag_when' => [
            'any_risk_5'              => 'CRITICAL: Block and address before building further',
            'two_or_more_risk_4'      => 'HIGH: Flag in output and suggest fixes',
            'financial_no_transaction'=> 'Auto-flag missing transactions for any financial domain',
            'no_auth_check'           => 'Auto-flag missing authorization for any CRUD operations',
        ],
    ],

];
