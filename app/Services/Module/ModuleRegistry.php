<?php

namespace App\Services\Module;

class ModuleRegistry
{
    private array $catalog = [
        'auth' => [
            'name'        => 'Authentication',
            'icon'        => '🔐',
            'category'    => 'core',
            'description' => 'Login, register, forgot password, email verification, remember me',
            'features'    => ['login', 'register', 'password_reset', 'email_verification'],
            'generates'   => ['controllers', 'views', 'routes'],
            'dependencies'=> [],
            'free'        => true,
            'popular'     => true,
            'ai_tokens'   => 0,
            'menu_items'  => [],  // infrastructure — no nav link needed
        ],
        'rbac' => [
            'name'        => 'Roles & Permissions (RBAC)',
            'icon'        => '🛡️',
            'category'    => 'core',
            'description' => 'Roles, permissions, policies, gate rules, admin middleware, HasRoles trait',
            'features'    => ['roles', 'permissions', 'policies', 'gates', 'middleware'],
            'generates'   => ['models', 'migrations', 'controllers', 'views', 'middleware', 'trait'],
            'dependencies'=> ['auth'],
            'free'        => true,
            'popular'     => true,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Roles & Permissions', 'route' => 'admin.roles.index', 'icon' => '🛡️', 'group' => 'Admin'],
            ],
        ],
        'payments' => [
            'name'        => 'Payment Gateway',
            'icon'        => '💳',
            'category'    => 'billing',
            'description' => 'Stripe, SSLCommerz, bKash with invoice, receipt PDF, refund support',
            'features'    => ['stripe', 'sslcommerz', 'bkash', 'invoice', 'receipt', 'refund'],
            'generates'   => ['models', 'migrations', 'controllers', 'views', 'services'],
            'dependencies'=> [],
            'free'        => true,
            'popular'     => true,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Payments',      'route' => 'payments.history',  'icon' => '💳', 'group' => 'Billing'],
                ['label' => 'Checkout',      'route' => 'payments.checkout', 'icon' => '🛍️', 'group' => 'Billing'],
            ],
        ],
        'notifications' => [
            'name'        => 'Notification System',
            'icon'        => '🔔',
            'category'    => 'communication',
            'description' => 'Database notifications, real-time bell icon, email, push, mark as read',
            'features'    => ['database_notifications', 'email', 'push', 'real_time_bell'],
            'generates'   => ['controllers', 'views', 'components', 'routes'],
            'dependencies'=> ['auth'],
            'free'        => true,
            'popular'     => true,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Notifications', 'route' => 'notifications.index', 'icon' => '🔔', 'group' => 'User'],
            ],
            'navbar_component' => 'notification-bell',  // auto-inject into navbar
        ],
        'media' => [
            'name'        => 'Media Manager',
            'icon'        => '🖼️',
            'category'    => 'utility',
            'description' => 'Drag-drop file upload, image resize, S3/local storage, gallery view',
            'features'    => ['file_upload', 'drag_drop', 'image_resize', 's3', 'gallery'],
            'generates'   => ['models', 'migrations', 'controllers', 'views'],
            'dependencies'=> [],
            'free'        => true,
            'popular'     => false,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Media Manager', 'route' => 'media.index', 'icon' => '🖼️', 'group' => 'Tools'],
            ],
        ],
        'reports' => [
            'name'        => 'Reports & Analytics',
            'icon'        => '📊',
            'category'    => 'analytics',
            'description' => 'Chart.js dashboards, KPI widgets, date range filters, export PDF/Excel',
            'features'    => ['charts', 'kpi_widgets', 'date_filter', 'pdf_export', 'excel_export'],
            'generates'   => ['controllers', 'views'],
            'dependencies'=> [],
            'free'        => true,
            'popular'     => true,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Analytics',     'route' => 'reports.dashboard', 'icon' => '📊', 'group' => 'Reports'],
            ],
        ],
        'inventory' => [
            'name'        => 'Inventory Management',
            'icon'        => '📦',
            'category'    => 'business',
            'description' => 'Stock tracking, low-stock alerts, warehouse locations, SKU, barcode',
            'features'    => ['stock_tracking', 'low_stock_alerts', 'warehouse', 'sku', 'barcode'],
            'generates'   => ['models', 'migrations', 'controllers', 'views', 'routes'],
            'dependencies'=> [],
            'free'        => true,
            'popular'     => false,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Inventory',     'route' => 'inventory.index',  'icon' => '📦', 'group' => 'Operations'],
                ['label' => 'Stock Alerts',  'route' => 'inventory.alerts', 'icon' => '⚠️', 'group' => 'Operations'],
            ],
        ],
        'orders' => [
            'name'        => 'Order Management',
            'icon'        => '🛒',
            'category'    => 'ecommerce',
            'description' => 'Order lifecycle, status pipeline, fulfillment, returns & refunds',
            'features'    => ['order_lifecycle', 'status_pipeline', 'fulfillment', 'returns'],
            'generates'   => ['models', 'migrations', 'controllers', 'views', 'routes'],
            'dependencies'=> ['payments'],
            'free'        => true,
            'popular'     => false,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Orders',        'route' => 'orders.index',  'icon' => '🛒', 'group' => 'Sales'],
                ['label' => 'Returns',       'route' => 'orders.returns','icon' => '↩️', 'group' => 'Sales'],
            ],
        ],
        'subscriptions' => [
            'name'        => 'Subscription Billing',
            'icon'        => '🔄',
            'category'    => 'billing',
            'description' => 'SaaS plans, recurring billing, trial periods, seat management, webhooks',
            'features'    => ['plans', 'recurring_billing', 'trials', 'seats', 'webhooks'],
            'generates'   => ['models', 'migrations', 'controllers', 'views', 'services'],
            'dependencies'=> ['payments', 'auth'],
            'free'        => true,
            'popular'     => false,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Plans',         'route' => 'subscriptions.plans',  'icon' => '🔄', 'group' => 'Billing'],
                ['label' => 'My Subscription','route'=> 'subscriptions.current','icon' => '💎', 'group' => 'Account'],
            ],
        ],
        'audit' => [
            'name'        => 'Audit Log',
            'icon'        => '📋',
            'category'    => 'compliance',
            'description' => 'Track user actions, IP logging, before/after change diff, export',
            'features'    => ['action_log', 'ip_tracking', 'change_diff', 'export', 'filter'],
            'generates'   => ['models', 'migrations', 'controllers', 'views', 'middleware', 'trait'],
            'dependencies'=> ['auth'],
            'free'        => true,
            'popular'     => false,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Audit Log',     'route' => 'admin.audit.index', 'icon' => '📋', 'group' => 'Admin'],
            ],
        ],
        'api' => [
            'name'        => 'REST API',
            'icon'        => '🔌',
            'category'    => 'integration',
            'description' => 'Sanctum token auth, versioning, rate limiting, JSON responses, Swagger ready',
            'features'    => ['sanctum', 'versioning', 'rate_limiting', 'json_responses'],
            'generates'   => ['controllers', 'routes', 'middleware', 'resources'],
            'dependencies'=> ['auth'],
            'free'        => true,
            'popular'     => true,
            'ai_tokens'   => 0,
            'menu_items'  => [],  // API is accessed via tokens, not nav
        ],
        'multi_tenant' => [
            'name'        => 'Multi-Tenancy',
            'icon'        => '🏢',
            'category'    => 'saas',
            'description' => 'Subdomain isolation, per-tenant DB scoping, custom domains, tenant settings',
            'features'    => ['subdomain', 'tenant_scope', 'per_tenant_db', 'custom_domain'],
            'generates'   => ['models', 'migrations', 'middleware', 'trait', 'routes'],
            'dependencies'=> ['auth', 'subscriptions'],
            'free'        => false,
            'popular'     => false,
            'ai_tokens'   => 0,
            'menu_items'  => [
                ['label' => 'Tenants',       'route' => 'tenants.index',    'icon' => '🏢', 'group' => 'Admin'],
                ['label' => 'Tenant Settings','route'=> 'tenants.settings', 'icon' => '⚙️', 'group' => 'Admin'],
            ],
        ],
    ];

    private array $agents = [
        'code_reviewer' => [
            'name'        => 'Code Reviewer',
            'icon'        => '🔍',
            'category'    => 'quality',
            'description' => 'Reviews generated code for bugs, dead code, and Laravel best practices',
            'model_tier'  => 'cheap',
            'free'        => true,
            'popular'     => true,
        ],
        'db_optimizer' => [
            'name'        => 'DB Optimizer',
            'icon'        => '⚡',
            'category'    => 'performance',
            'description' => 'Analyzes migrations, suggests indexes, detects N+1 queries, optimizes schema',
            'model_tier'  => 'cheap',
            'free'        => true,
            'popular'     => true,
        ],
        'security_agent' => [
            'name'        => 'Security Auditor',
            'icon'        => '🔒',
            'category'    => 'security',
            'description' => 'Scans for SQL injection, XSS, CSRF, exposed secrets, OWASP Top 10',
            'model_tier'  => 'premium',
            'free'        => true,
            'popular'     => true,
        ],
        'test_writer' => [
            'name'        => 'Test Writer',
            'icon'        => '🧪',
            'category'    => 'quality',
            'description' => 'Generates PHPUnit feature and unit tests for controllers, services, models',
            'model_tier'  => 'cheap',
            'free'        => true,
            'popular'     => false,
        ],
        'doc_writer' => [
            'name'        => 'Doc Writer',
            'icon'        => '📝',
            'category'    => 'documentation',
            'description' => 'Auto-generates API docs, README, inline comments, and Swagger annotations',
            'model_tier'  => 'free',
            'free'        => true,
            'popular'     => false,
        ],
        'seo_agent' => [
            'name'        => 'SEO Optimizer',
            'icon'        => '📈',
            'category'    => 'marketing',
            'description' => 'Adds meta tags, Open Graph, sitemaps, structured data, canonical URLs',
            'model_tier'  => 'free',
            'free'        => true,
            'popular'     => false,
        ],
    ];

    public function all(): array { return $this->catalog; }
    public function agents(): array { return $this->agents; }
    public function get(string $key): ?array { return $this->catalog[$key] ?? null; }
    public function getAgent(string $key): ?array { return $this->agents[$key] ?? null; }

    public function popular(): array
    {
        return array_values(array_filter(
            array_map(fn($k, $m) => array_merge($m, ['key' => $k]), array_keys($this->catalog), $this->catalog),
            fn($m) => $m['popular']
        ));
    }

    public function byCategory(): array
    {
        $grouped = [];
        foreach ($this->catalog as $key => $mod) {
            $grouped[$mod['category']][] = array_merge($mod, ['key' => $key]);
        }
        ksort($grouped);
        return $grouped;
    }

    public function forAppType(string $appType): array
    {
        $map = [
            'saas'        => ['auth', 'rbac', 'subscriptions', 'multi_tenant', 'notifications', 'api', 'audit'],
            'ecommerce'   => ['auth', 'orders', 'inventory', 'payments', 'notifications', 'media', 'reports'],
            'marketplace' => ['auth', 'rbac', 'orders', 'payments', 'notifications', 'media', 'reports'],
            'crm'         => ['auth', 'rbac', 'notifications', 'reports', 'audit', 'api'],
            'erp'         => ['auth', 'rbac', 'inventory', 'orders', 'payments', 'reports', 'audit'],
            'hrm'         => ['auth', 'rbac', 'notifications', 'reports', 'audit'],
            'lms'         => ['auth', 'rbac', 'subscriptions', 'notifications', 'media', 'reports'],
            'booking'     => ['auth', 'notifications', 'payments', 'reports'],
            'hospital'    => ['auth', 'rbac', 'notifications', 'reports', 'audit'],
            'restaurant'  => ['auth', 'payments', 'notifications', 'reports'],
            'accounting'  => ['auth', 'rbac', 'payments', 'reports', 'audit'],
            'school'      => ['auth', 'rbac', 'notifications', 'reports'],
            'inventory'   => ['auth', 'rbac', 'inventory', 'reports', 'audit'],
        ];
        $keys = $map[$appType] ?? ['auth', 'notifications', 'reports'];
        return array_values(array_filter(
            array_map(fn($k) => $this->get($k) ? array_merge($this->get($k), ['key' => $k]) : null, $keys)
        ));
    }

    /** Resolves full dependency chain for a set of module keys */
    public function resolveDependencies(array $keys): array
    {
        $resolved = [];
        foreach ($keys as $key) {
            $this->collectDeps($key, $resolved);
        }
        return array_keys($resolved);
    }

    private function collectDeps(string $key, array &$resolved): void
    {
        if (isset($resolved[$key])) return;
        $mod = $this->get($key);
        if (!$mod) return;
        foreach ($mod['dependencies'] as $dep) {
            $this->collectDeps($dep, $resolved);
        }
        $resolved[$key] = true;
    }
}
