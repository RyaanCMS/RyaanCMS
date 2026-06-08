<?php
/**
 * Build vs Buy Knowledge Base
 * Senior developers know what NOT to build from scratch.
 * "Smart AI says: use this package instead of generating it from scratch."
 * Level 5: Decision Knowledge — saves weeks of development
 */
return [

    // ── Always Use Package ─────────────────────────────────────────────────────
    'always_use_package' => [

        'authentication' => [
            'need'        => 'User login, register, 2FA, email verification',
            'use'         => 'Laravel Breeze / Fortify / Jetstream',
            'why'         => 'Battle-tested, handles edge cases you\'ll miss, security-audited',
            'never_build' => 'Custom auth from scratch — too many security footguns',
        ],

        'role_permissions' => [
            'need'    => 'RBAC, roles, permissions, teams',
            'use'     => 'spatie/laravel-permission',
            'why'     => '10M+ downloads, Laravel-native, cache-backed, team support',
            'setup'   => 'composer require spatie/laravel-permission → php artisan vendor:publish',
        ],

        'media_files' => [
            'need'    => 'File uploads, image resizing, conversions, responsive images',
            'use'     => 'spatie/laravel-medialibrary',
            'why'     => 'Handles conversions, disk abstraction, polymorphic attachment',
            'never_build' => 'Custom file management — edge cases (mime sniffing, resize, disk switching) are hard',
        ],

        'activity_audit_log' => [
            'need'    => 'Log user activities, who changed what, audit trail',
            'use'     => 'spatie/laravel-activitylog',
            'why'     => 'Simple API, polymorphic, stores diff, can log any model',
        ],

        'pdf_generation' => [
            'need'    => 'Generate PDF invoices, reports, certificates',
            'use'     => 'barryvdh/laravel-dompdf or knplabs/knp-snappy (wkhtmltopdf)',
            'why'     => 'HTML→PDF conversion is solved; building from scratch = months',
            'note'    => 'Always queue PDF generation for large documents',
        ],

        'excel_export_import' => [
            'need'    => 'Import/export CSV, Excel, data tables',
            'use'     => 'maatwebsite/laravel-excel',
            'why'     => '8M+ downloads, chunks large imports, handles all Excel formats',
        ],

        'full_text_search' => [
            'need'    => 'Product search, contact search, document search',
            'use'     => 'laravel/scout + meilisearch/meilisearch-php',
            'why'     => 'Meilisearch is free, self-hosted, typo-tolerant, instant',
            'never_build' => 'Custom search with raw LIKE queries for > 10K records',
        ],

        'payment_stripe' => [
            'need'    => 'Subscription billing, one-time payments, invoices',
            'use'     => 'laravel/cashier (Stripe)',
            'why'     => 'Handles subscription lifecycle, webhooks, invoicing, proration',
        ],

        'payment_bkash' => [
            'need'    => 'bKash payment gateway (Bangladesh)',
            'use'     => 'karim007/laravel-bkash-tokenize',
            'env_keys'=> ['BKASH_APP_KEY', 'BKASH_APP_SECRET', 'BKASH_USERNAME', 'BKASH_PASSWORD'],
        ],

        'payment_sslcommerz' => [
            'need'    => 'Multi-bank card + mobile banking (Bangladesh)',
            'use'     => 'sslcommerz/sslcommerz-laravel',
            'why'     => 'Widest bank coverage in Bangladesh, supports COD flow',
        ],

        'social_login' => [
            'need'    => 'Login with Google, Facebook, GitHub',
            'use'     => 'laravel/socialite',
            'setup'   => 'Add credentials to services.php, add social_id/provider to users table',
        ],

        'sms' => [
            'need'    => 'Send SMS notifications',
            'use'     => 'twilio/sdk (international) or vonage/client',
            'bd'      => 'Use custom HTTP integration for local BD SMS providers (SSL Wireless, Infobip)',
        ],

        'email' => [
            'need'    => 'Transactional emails',
            'use'     => 'SendGrid (SENDGRID_API_KEY) or Mailgun',
            'why'     => 'High deliverability, templates, tracking, bounce handling',
            'never_use_for_bulk' => 'Never use shared SMTP for bulk email — use dedicated provider',
        ],

        'push_notifications' => [
            'need'    => 'Mobile push notifications (Android + iOS)',
            'use'     => 'kreait/laravel-firebase (FCM)',
            'web_push'=> 'laravel-notification-channels/webpush',
        ],

        'multitenancy' => [
            'need'    => 'SaaS multi-tenancy',
            'use'     => 'stancl/tenancy (DB per tenant or shared DB)',
            'simple'  => 'For row-level: spatie/laravel-multitenancy',
        ],

        'queue_monitoring' => [
            'need'    => 'Monitor queues, jobs, failures',
            'use'     => 'laravel/horizon',
            'why'     => 'Real-time queue dashboard, auto-scaling, metrics',
        ],

        'api_debugging' => [
            'need'    => 'Debug queries, performance, mail in development',
            'use'     => 'laravel/telescope + barryvdh/laravel-debugbar',
            'note'    => 'Only install in dev/staging — remove from production or gate behind auth',
        ],

        'slugs_seo' => [
            'need'    => 'Unique URL slugs for products, posts, categories',
            'use'     => 'spatie/laravel-sluggable',
            'why'     => 'Handles uniqueness, regeneration, duplicate suffix automatically',
        ],

        'settings' => [
            'need'    => 'App/tenant settings key-value store',
            'use'     => 'spatie/laravel-settings or custom settings table',
        ],

        'backups' => [
            'need'    => 'Automated database + file backups',
            'use'     => 'spatie/laravel-backup',
            'setup'   => 'Schedule daily, store on S3/GCS, alert on failure',
        ],

        'translation' => [
            'need'    => 'Multi-language model content',
            'use'     => 'spatie/laravel-translatable',
        ],

        'charts' => [
            'need'    => 'Charts in Blade views',
            'use'     => 'Chart.js (lightweight) or ApexCharts (feature-rich)',
            'note'    => 'Import via CDN or npm — no Laravel-specific package needed',
        ],

        'data_tables' => [
            'need'    => 'Server-side data tables with sort/filter/search',
            'use'     => 'yajra/laravel-datatables-oracle or Livewire Volt tables',
        ],
    ],

    // ── Build Custom When ──────────────────────────────────────────────────────
    'build_custom_when' => [
        'core_business_logic'  => 'Your unique business rules that no package addresses',
        'competitive_advantage'=> 'Features that differentiate your product from competitors',
        'package_too_complex'  => 'When package API adds more complexity than solving it yourself',
        'performance_critical' => 'When package overhead is unacceptable for your use case',
        'licensing_issues'     => 'When package license conflicts with your business model',
    ],

    // ── Package Selection Criteria ─────────────────────────────────────────────
    'package_selection_criteria' => [
        'weekly_downloads'  => 'Choose packages with > 100K weekly Packagist downloads',
        'last_updated'      => 'Avoid packages not updated in 2+ years',
        'laravel_version'   => 'Ensure compatibility with your Laravel version',
        'issue_activity'    => 'Check GitHub issues — active maintainer responses matter',
        'stars'             => '> 1000 GitHub stars usually indicates community trust',
    ],

];
