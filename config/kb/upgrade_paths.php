<?php
/**
 * Upgrade Paths Knowledge Base
 * System evolution strategy — how to scale from simple to complex without rewriting.
 * "Build for today, architect for tomorrow's needs."
 * Level 5: Decision Knowledge — prevents architectural dead-ends
 */
return [

    // ── Core Principle ────────────────────────────────────────────────────────
    'principle' => 'Never over-engineer upfront. But ALWAYS keep the upgrade path clear.',

    // ── Architecture Upgrade Paths ─────────────────────────────────────────────
    'architecture' => [

        'monolith_to_modular' => [
            'when'   => '> 50K lines, multiple teams, slow deploy cycles',
            'steps'  => [
                '1. Introduce Service classes to move logic out of controllers',
                '2. Group by domain folder (User/, Order/, Inventory/)',
                '3. Use Events/Listeners for cross-domain communication',
                '4. Move to Domain-Driven Design (DDD) structure per module',
            ],
            'triggers'=> ['> 5 developers', 'Modules deploying on different cycles', 'Blast radius too large for changes'],
        ],

        'monolith_to_microservices' => [
            'when'   => 'Specific module needs independent scaling (e.g., video processing)',
            'steps'  => [
                '1. Identify the "Strangler Fig" candidate — one bounded context at a time',
                '2. Extract module behind an API interface',
                '3. Route traffic through API gateway',
                '4. Decommission the monolith\'s copy once stable',
            ],
            'warning'=> 'Microservices add: distributed tracing, network latency, deployment complexity. Start monolith.',
        ],

        'shared_db_to_separate_tenants' => [
            'when'   => 'Enterprise customers demand data isolation, compliance requires it',
            'steps'  => [
                '1. Ensure tenant_id on all tables + global scope (already done in row-level)',
                '2. Use stancl/tenancy with multi-db driver',
                '3. Migrate high-value tenants to dedicated DB first',
                '4. Automate DB provisioning on tenant create',
            ],
            'cost'   => 'High — every tenant DB needs backup, migration, maintenance separately',
        ],

        'synchronous_to_event_driven' => [
            'when'   => 'Slow response times, heavy interdependencies between modules',
            'steps'  => [
                '1. Identify slow operations (> 500ms) — email, PDF, 3rd party API calls',
                '2. Extract to queued Jobs (already have queue configured)',
                '3. Use Events + Listeners for cross-module notifications',
                '4. Consider Laravel Reverb/WebSocket for real-time frontend updates',
            ],
        ],
    ],

    // ── Database Upgrade Paths ─────────────────────────────────────────────────
    'database' => [

        'mysql_to_postgresql' => [
            'when'   => 'Need advanced JSON queries, better concurrency, PostGIS for geolocation',
            'effort' => 'Medium — mostly syntax compatible, but check: JSON queries, full-text search, date functions',
            'tool'   => 'pgloader for data migration',
        ],

        'no_cache_to_redis' => [
            'when'   => 'Page load > 500ms, repeated queries hitting DB',
            'steps'  => [
                '1. Install Redis, update CACHE_DRIVER=redis in .env',
                '2. Cache::remember() on expensive queries (reports, config)',
                '3. Add HTTP cache headers for public pages',
                '4. Session driver to redis for multi-server scaling',
            ],
        ],

        'no_search_to_meilisearch' => [
            'when'   => '> 10K searchable records, LIKE queries too slow',
            'steps'  => [
                '1. Add Laravel Scout to models (Searchable trait)',
                '2. Install Meilisearch locally / Docker',
                '3. php artisan scout:import to index existing data',
                '4. Replace old LIKE query with Model::search($query)->paginate()',
            ],
        ],

        'single_db_to_read_replica' => [
            'when'   => 'Read traffic dominating, write latency increasing',
            'steps'  => [
                '1. Set up replica (AWS RDS read replica, PlanetScale, etc.)',
                '2. database.connections.mysql.read / write config in Laravel',
                '3. ->onWriteConnection() for consistency-critical reads after write',
            ],
        ],
    ],

    // ── Auth Upgrade Paths ────────────────────────────────────────────────────
    'auth' => [

        'basic_auth_to_2fa' => [
            'when'   => 'Security audit, enterprise customer requirement, financial data involved',
            'steps'  => [
                '1. Add two_factor_secret, two_factor_recovery_codes columns to users',
                '2. Use laravel/fortify or pragmarx/google2fa-laravel',
                '3. Enforce for admin roles first, optional for regular users',
            ],
        ],

        'single_auth_to_sso' => [
            'when'   => 'Enterprise customers want Google Workspace / Azure AD login',
            'steps'  => [
                '1. Add social_provider, social_id to users table',
                '2. laravel/socialite + Google/Microsoft provider',
                '3. For SAML SSO: aacotroneo/laravel-saml2',
            ],
        ],

        'password_auth_to_passkey' => [
            'when'   => 'Modern UX, phishing-resistant login needed',
            'package'=> 'web-auth/webauthn-framework',
            'note'   => 'Progressive — allow password + passkey simultaneously during transition',
        ],
    ],

    // ── Deployment Upgrade Paths ──────────────────────────────────────────────
    'deployment' => [

        'shared_hosting_to_vps' => [
            'when'   => 'Queue workers needed, SSH access required, performance problems',
            'target' => 'DigitalOcean $6/mo Droplet or AWS t3.small',
            'stack'  => 'Nginx + PHP-FPM + MySQL + Redis + Supervisor for queues',
        ],

        'vps_to_containerized' => [
            'when'   => 'Multiple services, need auto-scaling, team size grows',
            'steps'  => [
                '1. Dockerize: Dockerfile + docker-compose.yml for local dev',
                '2. Push to container registry (ECR, GCR, Docker Hub)',
                '3. Deploy to: DigitalOcean App Platform, AWS ECS, or Kubernetes',
            ],
        ],

        'manual_deploy_to_ci_cd' => [
            'when'   => 'From day 1 — even solo developers benefit',
            'steps'  => [
                '1. GitHub Actions: run tests on PR',
                '2. Auto-deploy to staging on merge to main',
                '3. Manual approval gate → deploy to production',
            ],
            'tools' => 'GitHub Actions (free tier generous), Laravel Vapor (serverless), Ploi / Forge (server management)',
        ],

        'single_server_to_multi' => [
            'when'   => 'Traffic > 10K concurrent, uptime SLA requirements',
            'steps'  => [
                '1. Shared storage: move to S3 (already use Storage::disk)',
                '2. Session driver: Redis (shared across servers)',
                '3. Cache driver: Redis',
                '4. Load balancer: Nginx upstream or AWS ALB',
            ],
        ],
    ],

    // ── Feature Upgrade Paths ─────────────────────────────────────────────────
    'features' => [

        'manual_notification_to_notification_center' => [
            'when'   => 'Users missing notifications, no notification history',
            'steps'  => [
                '1. Add notifications table (already exists with Laravel notifications)',
                '2. Notification bell in header — count of unread',
                '3. Notification center page — mark read, filter by type',
                '4. User notification preferences (email, in-app, SMS toggle per type)',
            ],
        ],

        'basic_reports_to_analytics' => [
            'when'   => 'Business needs trend analysis, forecasting, cohorts',
            'steps'  => [
                '1. Add aggregation jobs (daily/weekly summaries stored to analytics tables)',
                '2. Chart.js or ApexCharts for visualization',
                '3. Export to Excel with maatwebsite/excel',
                '4. BI tool integration (Metabase, Grafana) for power users',
            ],
        ],

        'single_currency_to_multi_currency' => [
            'when'   => 'International expansion',
            'steps'  => [
                '1. Add currency column to orders, invoices',
                '2. Store exchange rate at transaction time (never recalculate)',
                '3. ExchangeRate-API or Open Exchange Rates for live rates',
                '4. Display in user\'s currency, settle in base currency',
            ],
        ],
    ],

];
