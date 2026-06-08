<?php
/**
 * Feature Dependency Knowledge Base
 * AI automatically understands what each feature requires.
 * "If user asks for X, they also need Y and Z."
 */
return [

    // ── Core Feature Dependencies ─────────────────────────────────────────────
    'dependencies' => [

        'subscription_billing' => [
            'requires' => ['users', 'auth', 'plans_table', 'payment_gateway', 'invoices'],
            'recommends' => ['email_notifications', 'webhooks', 'trial_management'],
            'packages' => ['laravel/cashier'],
            'note' => 'Without payment gateway, subscription cannot charge',
        ],

        'multi_tenancy' => [
            'requires' => ['tenants_table', 'tenant_id_on_all_tables', 'global_scope', 'tenant_middleware'],
            'recommends' => ['subdomain_routing', 'tenant_settings', 'tenant_storage_isolation'],
            'packages' => ['stancl/tenancy or spatie/laravel-multitenancy'],
        ],

        'role_based_access' => [
            'requires' => ['users', 'roles_table', 'permissions_table', 'role_user_pivot'],
            'recommends' => ['policies', 'middleware_role_check', 'permission_cache'],
            'packages' => ['spatie/laravel-permission'],
        ],

        'ecommerce_orders' => [
            'requires' => ['products', 'product_variants', 'customers', 'cart', 'orders', 'order_items', 'addresses'],
            'recommends' => ['coupons', 'shipping_methods', 'tax_rules', 'inventory_tracking'],
            'note' => 'Orders need inventory decremented atomically via DB transaction',
        ],

        'payment_processing' => [
            'requires' => ['payments_table', 'webhook_handler', 'idempotency_key'],
            'recommends' => ['payment_logs', 'refunds_table', 'retry_logic'],
            'critical' => 'ALWAYS use webhook for confirmation — never trust redirect-only',
        ],

        'file_uploads' => [
            'requires' => ['storage_configuration', 'file_validation', 'private_disk'],
            'recommends' => ['image_resize_job', 'virus_scan', 'signed_urls', 'media_library'],
            'packages' => ['spatie/laravel-medialibrary'],
        ],

        'notifications' => [
            'requires' => ['users', 'notifications_table (if DB driver)', 'queue_configured'],
            'channels' => ['mail', 'database', 'broadcast', 'sms', 'push'],
            'note' => 'Always queue notifications — never send in request lifecycle',
        ],

        'search' => [
            'requires' => ['searchable_model', 'scout_configuration'],
            'small_scale' => 'database driver (LIKE queries)',
            'medium_scale' => 'Meilisearch (self-hosted, free)',
            'large_scale' => 'Elasticsearch or Algolia',
            'packages' => ['laravel/scout', 'meilisearch/meilisearch-php'],
        ],

        'real_time_features' => [
            'requires' => ['websocket_server', 'broadcasting_config', 'queue_worker'],
            'packages' => ['laravel/reverb (built-in) or pusher/pusher-php-server'],
            'note' => 'Redis required for broadcasting with multiple servers',
        ],

        'reports_export' => [
            'requires' => ['reportable_models', 'queue_for_large_exports'],
            'formats' => ['PDF (dompdf/barryvdh-laravel-dompdf)', 'Excel (maatwebsite/excel)', 'CSV (manual)'],
            'note' => 'Reports > 1000 rows MUST be queued and emailed/downloaded',
        ],

        'audit_trail' => [
            'requires' => ['audits_table', 'authenticatable_model'],
            'packages' => ['owen-it/laravel-auditing or spatie/laravel-activitylog'],
            'captures' => ['old_values', 'new_values', 'user_id', 'ip_address', 'event_type'],
        ],

        'multi_language' => [
            'requires' => ['lang_files_per_locale', 'locale_middleware', 'translatable_models'],
            'packages' => ['spatie/laravel-translatable', 'mcamara/laravel-localization'],
            'note' => 'Add locale column to translatable tables or use JSON column',
        ],

        'api_authentication' => [
            'requires' => ['sanctum_installed', 'token_abilities'],
            'for_mobile' => 'Bearer token + refresh token flow',
            'for_spa'    => 'Cookie-based Sanctum + CSRF',
        ],

        'two_factor_auth' => [
            'requires' => ['two_factor_columns_on_users', 'fortify_or_custom'],
            'methods' => ['TOTP (Google Authenticator)', 'SMS OTP', 'Email OTP'],
            'packages' => ['laravel/fortify'],
        ],

        'soft_delete_with_restore' => [
            'requires' => ['deleted_at column', 'SoftDeletes trait'],
            'recommends' => ['withTrashed() scopes', 'restore UI for admins'],
            'note' => 'Add to: users, orders, invoices, products, employees',
        ],

        'queue_processing' => [
            'requires' => ['queue_driver (redis/database)', 'worker_process', 'failed_jobs_table'],
            'dev' => 'database driver',
            'production' => 'Redis driver + Supervisor to keep workers alive',
        ],

        'geolocation' => [
            'requires' => ['lat/lng columns on address', 'maps_api_key'],
            'packages' => ['spatie/geocoder'],
            'note' => 'Store coordinates for efficient proximity queries (Haversine formula)',
        ],

        'inventory_tracking' => [
            'requires' => ['products', 'stock_movements_table', 'warehouses'],
            'note' => 'Stock changes MUST be atomic transactions — never direct decrement without lock',
            'pattern' => 'Pessimistic locking: DB::transaction + lockForUpdate() on stock update',
        ],

        'approval_workflow' => [
            'requires' => ['approvable_model', 'approvals_table', 'approvers_config', 'notification_system'],
            'states' => ['pending', 'approved', 'rejected', 'escalated'],
            'note' => 'Define approval levels per amount/type in config, not hardcoded',
        ],
    ],

    // ── Project Type → Required Features ─────────────────────────────────────
    'project_required_features' => [

        'ecommerce' => [
            'must_have' => ['auth', 'rbac', 'products', 'cart', 'orders', 'payments', 'inventory', 'notifications', 'reports'],
            'should_have' => ['coupons', 'reviews', 'wishlist', 'shipping_tracking', 'seo'],
            'nice_to_have' => ['recommendations', 'loyalty_points', 'live_chat'],
        ],

        'crm' => [
            'must_have' => ['auth', 'rbac', 'contacts', 'companies', 'deals', 'activities', 'pipeline', 'reports'],
            'should_have' => ['tasks', 'email_integration', 'file_attachments', 'notes'],
            'nice_to_have' => ['ai_scoring', 'forecasting', 'marketing_automation'],
        ],

        'hrm' => [
            'must_have' => ['auth', 'rbac', 'employees', 'departments', 'attendance', 'leaves', 'payroll', 'notifications'],
            'should_have' => ['recruitment', 'performance', 'training', 'documents', 'self_service'],
            'nice_to_have' => ['biometric_integration', 'ai_scheduling', 'succession_planning'],
        ],

        'hospital' => [
            'must_have' => ['auth', 'rbac', 'patients', 'doctors', 'appointments', 'emr', 'billing', 'pharmacy', 'audit_trail'],
            'should_have' => ['lab_module', 'radiology', 'nursing_notes', 'insurance_claims'],
            'nice_to_have' => ['telemedicine', 'mobile_app', 'equipment_maintenance'],
            'critical' => 'Patient data is HIPAA-sensitive — encryption + strict audit trail mandatory',
        ],

        'saas' => [
            'must_have' => ['auth', 'multi_tenancy', 'rbac', 'subscription_billing', 'team_management', 'onboarding', 'notifications'],
            'should_have' => ['usage_tracking', 'feature_flags', 'audit_trail', 'api'],
            'nice_to_have' => ['white_label', 'affiliate_system', 'sso'],
        ],

        'school' => [
            'must_have' => ['auth', 'rbac', 'students', 'classes', 'attendance', 'exams', 'fees', 'reports', 'notifications'],
            'should_have' => ['library', 'transport', 'parent_portal', 'timetable'],
            'nice_to_have' => ['online_classes', 'assignments', 'mobile_app'],
        ],

        'restaurant' => [
            'must_have' => ['auth', 'rbac', 'menu', 'orders', 'tables', 'billing', 'kitchen_display', 'inventory_basic'],
            'should_have' => ['reservations', 'online_ordering', 'delivery_tracking', 'loyalty'],
            'nice_to_have' => ['waiter_app', 'food_cost_analysis', 'customer_feedback'],
        ],

        'marketplace' => [
            'must_have' => ['auth', 'rbac', 'vendors', 'products', 'orders', 'multi_vendor_cart', 'commission', 'payouts', 'reviews'],
            'should_have' => ['vendor_analytics', 'dispute_management', 'featured_listings', 'search_filters'],
            'note' => 'Multi-vendor cart splits orders by vendor — complex — plan carefully',
        ],
    ],

    // ── Feature Conflict Rules ─────────────────────────────────────────────────
    'feature_conflicts' => [
        'monolith_and_microservices' => 'Choose one architecture — do not mix',
        'row_level_and_db_per_tenant' => 'Choose one tenancy strategy — migrating is hard',
        'session_auth_and_stateless_api' => 'Stateless API should use token auth, not sessions',
    ],

];
