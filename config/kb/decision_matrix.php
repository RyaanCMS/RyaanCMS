<?php
/**
 * Decision Matrix KB — "When to use WHAT and WHY"
 * This is Level 5 of the Senior Dev Pyramid: Decision Knowledge
 * A junior dev knows HOW to code; a senior architect knows WHEN, WHY, and WHAT trade-offs exist.
 */
return [

    // ── Architecture Selection ────────────────────────────────────────────────
    'architecture' => [
        'monolith' => [
            'when'     => ['users < 50K', 'single team ≤ 5 devs', 'MVP/v1', 'budget constrained', 'simple domain'],
            'stack'    => 'Laravel + Blade/Livewire + MySQL',
            'pros'     => ['Fast development', 'Easy debugging', 'Single deploy', 'Low ops cost'],
            'cons'     => ['Hard to scale teams', 'Risky big refactors'],
            'examples' => ['school management', 'clinic', 'small CRM', 'internal tool'],
        ],
        'modular_monolith' => [
            'when'     => ['users 10K–500K', '3–15 modules', 'multiple domains', 'SaaS', 'growing team'],
            'stack'    => 'Laravel Modules (nwidart) + PostgreSQL + Redis',
            'pros'     => ['Domain isolation', 'Team parallel work', 'Extractable to micro later'],
            'cons'     => ['Module boundary discipline required'],
            'examples' => ['ERP', 'HRM+Payroll', 'multi-module SaaS', 'marketplace'],
            'default_for' => ['saas', 'erp', 'crm_large', 'ecommerce', 'hrm'],
        ],
        'event_driven' => [
            'when'     => ['complex workflows', 'audit trail required', 'domain events matter', 'async processes heavy'],
            'stack'    => 'Laravel Events + Jobs + Queues + Redis Pub/Sub',
            'examples' => ['order lifecycle', 'payment processing', 'notification system'],
        ],
        'api_spa' => [
            'when'     => ['mobile app required', 'separate frontend team', 'public API needed', 'SPA UX demanded'],
            'stack'    => 'Laravel API + Sanctum/Passport + React/Next.js',
            'examples' => ['B2C app', 'marketplace with mobile', 'public platform'],
        ],
        'microservices' => [
            'when'     => ['users > 1M', 'independent teams > 10', 'need polyglot DB', 'high availability SLA'],
            'stack'    => 'Docker + Kubernetes + API Gateway + Event Bus',
            'warning'  => 'Do NOT use microservices prematurely — it adds huge ops complexity',
        ],
    ],

    // ── Database Selection ────────────────────────────────────────────────────
    'database' => [
        'mysql' => [
            'when'    => ['simple CRUD', 'shared hosting', 'small-medium scale', 'tight budget'],
            'version' => 'MySQL 8.0+ (for JSON support, window functions)',
        ],
        'postgresql' => [
            'when'    => ['JSON/JSONB columns', 'full-text search', 'complex queries', 'SaaS multi-tenant', 'financial data'],
            'extras'  => ['UUID primary keys', 'ENUM types', 'Array columns', 'Row-level security'],
            'default_for' => ['saas', 'financial', 'reporting_heavy', 'multi_tenant'],
        ],
        'redis' => [
            'always_use_for' => ['sessions', 'cache', 'queues', 'rate limiting', 'real-time counters', 'leaderboards'],
            'optional_for'   => ['pub/sub', 'job locking', 'feature flags'],
        ],
        'elasticsearch' => [
            'when' => ['full-text search > 100K records', 'log analytics', 'faceted product search'],
        ],
    ],

    // ── Scale Thresholds ──────────────────────────────────────────────────────
    'scale' => [
        'small'  => ['users' => '< 10K',    'arch' => 'monolith',          'db' => 'MySQL',       'cache' => 'Redis optional', 'queue' => 'database driver OK'],
        'medium' => ['users' => '10K–100K',  'arch' => 'modular_monolith',  'db' => 'PostgreSQL',  'cache' => 'Redis required', 'queue' => 'Redis queue'],
        'large'  => ['users' => '100K–1M',   'arch' => 'modular_monolith',  'db' => 'PostgreSQL',  'cache' => 'Redis+CDN',      'queue' => 'Redis queue + workers'],
        'xlarge' => ['users' => '> 1M',      'arch' => 'microservices',     'db' => 'Polyglot',    'cache' => 'Redis Cluster',  'queue' => 'Kafka/RabbitMQ'],
    ],

    // ── Async / Queue Rules ───────────────────────────────────────────────────
    'async_rules' => [
        'always_queue' => [
            'emails', 'SMS', 'PDF generation', 'image processing/resize',
            'bulk CSV import/export', 'webhook dispatching', 'report generation',
            'search index updates', 'notifications (push/email)', 'invoice generation',
        ],
        'consider_queue' => ['analytics events', 'audit log writes', 'third-party API calls'],
        'keep_sync'      => ['authentication', 'payment intent creation', 'CRUD reads', 'search queries'],
    ],

    // ── Frontend / Rendering Strategy ─────────────────────────────────────────
    'frontend' => [
        'blade'          => ['when' => 'Admin panels, simple CRUD, internal tools'],
        'livewire'       => ['when' => 'Reactive forms, real-time validation, data tables with sort/filter — NO full SPA needed'],
        'inertia_react'  => ['when' => 'SPA UX, complex state, mobile app planned later (share logic)'],
        'api_react_spa'  => ['when' => 'Separate mobile team, public API consumers, enterprise frontend team'],
        'next_js'        => ['when' => 'SEO required + SPA UX, marketing pages + app hybrid'],
    ],

    // ── Multi-Tenancy Approach ────────────────────────────────────────────────
    'multitenancy' => [
        'row_level' => [
            'when'   => ['cost-sensitive SaaS', 'small-medium tenants', 'simple isolation OK'],
            'how'    => 'tenant_id column on ALL tables + global scope BelongsToTenant',
            'caveat' => 'One DB breach = all tenants exposed — mitigate with RLS in PostgreSQL',
        ],
        'schema_per_tenant' => [
            'when'   => ['medium-large tenants', 'need schema separation', 'PostgreSQL only'],
            'how'    => 'Create schema per tenant, switch search_path per request',
        ],
        'database_per_tenant' => [
            'when'   => ['enterprise clients', 'GDPR/compliance required', 'willing to pay extra ops'],
            'how'    => 'Separate DB connection per tenant, Tenant model with db_host/db_name',
        ],
        'default_recommendation' => 'row_level for new SaaS — migrate to schema if you hit 500+ tenants',
    ],

    // ── Authentication Strategy ───────────────────────────────────────────────
    'auth' => [
        'sanctum'  => ['when' => 'SPA + mobile (same domain or token), simple API auth'],
        'passport' => ['when' => 'OAuth 2.0 server, third-party clients, public API'],
        'jwt'      => ['when' => 'Stateless API only, no session needed'],
        'breeze'   => ['when' => 'Quick auth scaffold for Blade apps'],
        'fortify'  => ['when' => 'Custom auth flow with 2FA, email verify, password confirm'],
        'social'   => ['package' => 'laravel/socialite', 'when' => 'Google/GitHub/Facebook login needed'],
    ],

    // ── Payment Gateway Selection ─────────────────────────────────────────────
    'payments' => [
        'bangladesh' => [
            'default'  => 'SSLCommerz (widest bank coverage)',
            'mobile'   => 'bKash + Nagad (70%+ mobile payments)',
            'card'     => 'SSLCommerz or AamarPay',
            'fallback' => 'ShurjoPay',
        ],
        'global'     => 'Stripe (best DX, webhooks, subscription support)',
        'india'      => 'Razorpay',
        'africa'     => 'M-Pesa or Flutterwave',
        'rule'       => 'Always implement webhooks — never trust redirect-only confirmation',
    ],

    // ── Search Implementation ─────────────────────────────────────────────────
    'search' => [
        'records < 10K'    => 'Laravel Scout + database driver (LIKE search)',
        'records 10K–500K' => 'Laravel Scout + Meilisearch (self-hosted, free)',
        'records > 500K'   => 'Elasticsearch or Algolia',
        'rule'             => 'Always index on: name, email, phone, SKU, reference number',
    ],

];
