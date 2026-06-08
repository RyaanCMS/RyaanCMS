<?php
/**
 * SaaS Architecture Knowledge Base
 * Multi-tenant, subscription billing, feature limits, teams, workspaces
 */
return [

    // ── SaaS Architecture Fundamentals ───────────────────────────────────────
    'fundamentals' => [
        'tenancy_model'   => 'Row-level isolation: tenant_id on all tables (default for new SaaS)',
        'package'         => 'spatie/laravel-multitenancy or stancl/tenancy',
        'onboarding_flow' => 'Register → Verify Email → Create Workspace/Company → Invite Team → Start Trial',
        'url_strategies'  => [
            'subdomain' => '{tenant}.app.com — best for isolation feel, hard to setup SSL wildcard',
            'path'      => 'app.com/{tenant} — simplest, good for small SaaS',
            'custom_domain' => '{client-domain.com} — enterprise feature, complex SSL management',
        ],
    ],

    // ── Subscription & Billing ────────────────────────────────────────────────
    'billing' => [
        'package'         => 'laravel/cashier (Stripe) or laravel/cashier-paddle',
        'tables'          => ['plans', 'subscriptions', 'subscription_items', 'invoices'],
        'plan_structure'  => [
            'columns' => ['id', 'name', 'slug', 'price_monthly', 'price_yearly', 'trial_days',
                          'features (JSON)', 'limits (JSON)', 'stripe_price_id', 'is_active'],
        ],
        'subscription_lifecycle' => [
            'trial'    => 'New tenant gets X days free trial automatically',
            'active'   => 'Payment method on file, subscription active',
            'past_due' => 'Payment failed — grace period 3–7 days, then suspend',
            'canceled' => 'Access until period end, then downgrade to free tier',
        ],
        'pricing_models' => [
            'flat_rate'   => 'Simple: $X/month for all features (best for small apps)',
            'per_seat'    => 'X/user/month (CRM, project management)',
            'usage_based' => 'Pay for what you use (API calls, storage, SMS)',
            'freemium'    => 'Free tier + paid for premium features',
            'tiered'      => 'Starter/Pro/Enterprise with feature locks per tier',
        ],
    ],

    // ── Feature Limits ───────────────────────────────────────────────────────
    'feature_limits' => [
        'implementation' => 'Store limits in plans.limits JSON column',
        'check_pattern'  => '$tenant->plan->limit("max_users") vs $tenant->users()->count()',
        'examples'       => [
            'max_users'    => 'Starter: 5, Pro: 25, Enterprise: unlimited (-1)',
            'max_projects' => 'Starter: 3, Pro: unlimited',
            'storage_gb'   => 'Starter: 1, Pro: 50, Enterprise: 500',
            'api_calls_monthly' => 'Starter: 1000, Pro: 50000',
        ],
        'middleware'     => 'CheckFeatureLimit middleware — throw 403 with upgrade message',
        'ui'             => 'Show usage progress bar. Warn at 80%. Lock at 100% with upgrade CTA.',
    ],

    // ── Teams & Workspaces ────────────────────────────────────────────────────
    'teams' => [
        'package'     => 'spatie/laravel-permission per-team or Jetstream teams',
        'roles'       => ['owner', 'admin', 'manager', 'member', 'guest'],
        'invitations' => 'Email invite → unique token link → accept → join team with role',
        'permissions' => 'Scoped: team_id + role determines what user can do within that team',
        'tables'      => ['teams', 'team_user (pivot: team_id, user_id, role)', 'team_invitations'],
    ],

    // ── Usage Tracking ────────────────────────────────────────────────────────
    'usage_tracking' => [
        'table'   => 'usage_records: tenant_id, metric, amount, recorded_at',
        'metrics' => ['api_calls', 'storage_used', 'emails_sent', 'sms_sent', 'active_users'],
        'method'  => 'Increment via Redis counter → flush to DB periodically',
        'billing' => 'Sum usage_records for billing period → Stripe usage-based billing',
    ],

    // ── Tenant Isolation Checklist ────────────────────────────────────────────
    'isolation_checklist' => [
        'global_scope'   => 'BelongsToTenant trait with boot() scoping all queries to current tenant',
        'foreign_keys'   => 'Every table FK to tenant verified in policy',
        'file_storage'   => 'Tenant files in {disk}/{tenant_id}/ folder',
        'cache_keys'     => 'Prefix cache keys with tenant_id: "t:{$id}:settings"',
        'queue_context'  => 'Store tenant_id in job payload — restore context in handle()',
        'event_context'  => 'Include tenant_id in all domain events',
        'logging'        => 'Log::withContext(["tenant_id" => $id]) in middleware',
    ],

    // ── Onboarding Flow ──────────────────────────────────────────────────────
    'onboarding' => [
        'steps' => [
            1 => 'Registration (name, email, company, password)',
            2 => 'Email verification',
            3 => 'Company/workspace setup (logo, timezone, currency)',
            4 => 'First feature walkthrough (empty state with guided tour)',
            5 => 'Invite team members',
            6 => 'Connect integrations (if applicable)',
            7 => 'Start building value',
        ],
        'first_run_experience' => 'Show empty states with action buttons. Pre-populate with sample data.',
        'trial_notification'   => 'Email at trial start, day 3, day 7 (expires), day 8 (expired)',
    ],

    // ── SaaS Monitoring ──────────────────────────────────────────────────────
    'monitoring' => [
        'business_metrics' => ['MRR', 'ARR', 'Churn rate', 'CAC', 'LTV', 'Trial conversion rate'],
        'technical_metrics' => ['Tenant query performance', 'Per-tenant storage usage', 'API usage per tenant'],
        'alerts'           => ['Trial ending', 'Payment failed', 'Usage limit approaching', 'Large tenant slow queries'],
    ],

];
