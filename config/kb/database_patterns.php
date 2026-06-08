<?php
/**
 * Database Design Knowledge Base
 * Senior developers think about the database FIRST.
 */
return [

    // ── Core Design Rules ─────────────────────────────────────────────────────
    'design_rules' => [
        'normalization'     => 'Start with 3NF. Denormalize only for proven performance needs with data.',
        'naming'            => 'snake_case table names, plural. Columns: snake_case, singular descriptive.',
        'primary_keys'      => 'id BIGINT auto-increment default. Use UUID for public-facing/distributed systems.',
        'foreign_keys'      => 'ALWAYS define FK constraints. Set cascade rules explicitly.',
        'indexes'           => 'Index: all FK columns, all WHERE columns, all ORDER BY columns.',
        'timestamps'        => 'created_at, updated_at on ALL tables. Add created_by for audit-sensitive tables.',
        'soft_delete'       => 'deleted_at for: users, orders, invoices, products, any recoverable entity.',
        'status_fields'     => "Use ENUM or VARCHAR with constants — never raw integers for status.",
        'money'             => 'Store money as DECIMAL(15,2) or INTEGER cents. NEVER FLOAT (floating point errors).',
        'phone'             => "VARCHAR(20) for phone — handle country codes, formatting.",
        'email'             => "VARCHAR(255), lowercase before store, unique index.",
    ],

    // ── Essential Patterns ────────────────────────────────────────────────────
    'patterns' => [

        'audit_table' => [
            'description' => 'Track every change to sensitive data',
            'columns'     => ['id', 'auditable_type', 'auditable_id', 'event (created/updated/deleted)',
                              'old_values (JSON)', 'new_values (JSON)', 'user_id', 'ip_address', 'created_at'],
            'use_for'     => ['payments', 'user roles', 'prices', 'invoices', 'stock levels'],
            'package'     => 'owen-it/laravel-auditing',
        ],

        'settings_table' => [
            'description' => 'Key-value store for app/tenant settings',
            'columns'     => ['id', 'group', 'key', 'value (text)', 'type (string/boolean/json)', 'updated_at'],
            'index'       => ['group', 'key'],
        ],

        'activity_log' => [
            'description' => 'User activity tracking',
            'columns'     => ['id', 'user_id', 'description', 'subject_type', 'subject_id', 'properties (JSON)', 'ip', 'created_at'],
            'package'     => 'spatie/laravel-activitylog',
        ],

        'polymorphic_address' => [
            'description' => 'Reusable address for users, companies, branches',
            'columns'     => ['id', 'addressable_type', 'addressable_id', 'label (home/work/shipping)',
                              'line1', 'line2', 'city', 'state', 'country', 'postal_code', 'is_default'],
        ],

        'media_library' => [
            'description' => 'Polymorphic file/image storage',
            'package'     => 'spatie/laravel-medialibrary',
            'columns'     => ['id', 'model_type', 'model_id', 'collection_name', 'file_name', 'mime_type', 'disk', 'size', 'conversions_disk', 'created_at'],
        ],

        'translations_table' => [
            'description' => 'Multi-language content support',
            'pattern'     => 'product_translations table: product_id, locale, name, description',
            'package'     => 'spatie/laravel-translatable',
        ],
    ],

    // ── Indexing Strategy ─────────────────────────────────────────────────────
    'indexing' => [
        'rule_1'     => 'Every foreign key column gets an index automatically (FK constraint)',
        'rule_2'     => 'Columns in WHERE clauses → single column index',
        'rule_3'     => 'Columns in ORDER BY that appear with filtered WHERE → composite index',
        'rule_4'     => 'Unique constraints: email, phone, reference numbers',
        'rule_5'     => 'Full-text index for searchable text fields (name, description)',
        'composite'  => 'Order matters: (tenant_id, status, created_at) — put high-cardinality first',
        'anti_index' => 'Do not index every column — write penalty, storage cost, optimizer confusion',

        'common_examples' => [
            'orders'   => "index(['user_id', 'status']), index(['created_at']), index(['reference_number'])",
            'products' => "index(['category_id', 'status']), index(['sku']), fulltext(['name', 'description'])",
            'users'    => "unique('email'), index('phone'), index('status')",
        ],
    ],

    // ── Multi-Tenant Schema ────────────────────────────────────────────────────
    'multi_tenant' => [
        'every_table_needs' => 'tenant_id BIGINT UNSIGNED NOT NULL + index + FK to tenants',
        'global_scope'      => 'BelongsToTenant trait with ScopedBy tenant_id boot',
        'exceptions'        => 'tenants, plans, countries, currencies — these are global',
        'rule'              => 'Boot global scope in AppServiceProvider or via trait — never rely on manual where clauses',
    ],

    // ── Financial Data Patterns ────────────────────────────────────────────────
    'financial' => [
        'amount_storage'  => 'DECIMAL(15,2) for currencies. Or store as INTEGER cents (100 = $1.00).',
        'currency'        => "Always store currency_code (USD, BDT) alongside amount",
        'exchange_rates'  => "Separate exchange_rates table — never hard-code",
        'double_entry'    => 'For accounting: double-entry ledger pattern (debit/credit journal entries)',
        'immutable_records'=> 'Financial records (invoices, payments) should NEVER be updated — void+reissue instead',
    ],

    // ── Common Schema Patterns by Domain ─────────────────────────────────────
    'domain_schemas' => [

        'ecommerce_core' => [
            'tables' => ['products', 'product_variants', 'categories', 'customers', 'orders',
                         'order_items', 'payments', 'shipping_addresses', 'coupons', 'reviews'],
            'key_relationships' => [
                'Order → OrderItems (hasMany)',
                'OrderItem → Product/ProductVariant (belongsTo)',
                'Order → Customer (belongsTo)',
                'Order → Payment (hasOne)',
            ],
        ],

        'crm_core' => [
            'tables' => ['contacts', 'companies', 'deals', 'activities', 'notes',
                         'pipelines', 'pipeline_stages', 'tasks', 'emails'],
            'key_relationships' => [
                'Contact → Company (belongsTo)',
                'Deal → Contact/Company (belongsTo)',
                'Deal → PipelineStage (belongsTo)',
                'Activity polymorphic → Contact/Deal/Company',
            ],
        ],

        'hrm_core' => [
            'tables' => ['employees', 'departments', 'designations', 'attendance',
                         'leaves', 'payrolls', 'payroll_items', 'performance_reviews'],
            'key_relationships' => [
                'Employee → Department (belongsTo)',
                'Employee → Designation (belongsTo)',
                'Attendance → Employee (belongsTo)',
                'Payroll → Employee + PayrollItems',
            ],
        ],

        'saas_core' => [
            'tables' => ['tenants', 'plans', 'subscriptions', 'subscription_items',
                         'invoices', 'invoice_items', 'usage_records', 'feature_flags'],
            'key_relationships' => [
                'Tenant → Plan via Subscription',
                'Subscription → Invoice (hasMany)',
                'UsageRecord → Tenant + Feature',
            ],
        ],
    ],

];
