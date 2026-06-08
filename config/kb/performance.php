<?php
/**
 * Performance Knowledge Base
 * Senior developers think about scale BEFORE it becomes a problem.
 */
return [

    // ── Always-Apply Performance Rules ───────────────────────────────────────
    'always_apply' => [
        'eager_loading'   => "ALWAYS with() relationships. NEVER lazy-load in loops. Use withCount() for counts.",
        'pagination'      => "paginate(25) or cursorPaginate() everywhere. NEVER ::all() on tables > 1K rows.",
        'select_columns'  => "select(['id','name','email']) — don't fetch columns you don't use.",
        'cache_config'    => "Cache::remember('key', 3600, fn() => ...) for expensive reads.",
        'queue_heavy_ops' => "Email, SMS, PDF, CSV export, image processing → always dispatch Job.",
        'avoid_n_plus_1'  => "Install Laravel Debugbar in dev. Fix ALL N+1 before deploy.",
        'index_fks'       => "Every foreign key column + every WHERE column must be indexed.",
    ],

    // ── Caching Strategy ─────────────────────────────────────────────────────
    'caching' => [
        'driver_selection' => [
            'development' => 'file or array driver',
            'production'  => 'Redis (required for multi-server, queues, sessions)',
        ],
        'cache_hierarchy' => [
            'L1_request' => 'Static in-memory: static $cache in service (per-request, no Redis hit)',
            'L2_redis'   => 'Redis cache: 5min–24hr for computed data',
            'L3_db'      => 'Source of truth — only hit when cache misses',
        ],
        'what_to_cache' => [
            'settings'       => 'App/tenant settings — change rarely, read every request',
            'menu_items'     => 'Navigation menus, permissions list',
            'reports'        => 'Daily/weekly reports — regenerate on schedule',
            'rate_limits'    => 'Redis counters for API rate limiting',
            'user_roles'     => 'User permissions/roles — TTL 10min, clear on role change',
        ],
        'cache_tags' => 'Use cache tags (Redis only) to invalidate related cache groups at once',
        'never_cache' => ['passwords', 'tokens', 'PII without encryption', 'real-time data'],
    ],

    // ── Queue Best Practices ──────────────────────────────────────────────────
    'queues' => [
        'driver'     => 'Redis for production. Database driver OK for small apps.',
        'workers'    => 'Run: php artisan queue:work --sleep=3 --tries=3 --max-time=3600',
        'supervisor' => 'Use Supervisor to keep workers running and auto-restart on crash',
        'queues_priority' => [
            'high'   => 'Payment processing, critical notifications',
            'default'=> 'Emails, SMS',
            'low'    => 'Reports, analytics, bulk operations',
        ],
        'failed_jobs' => 'failed_jobs table + horizon or queue:failed monitoring. Alert on spike.',
        'batching'    => 'Bus::batch() for parallel jobs with progress tracking and then/catch callbacks',
        'chaining'    => 'Job::withChain() for sequential dependent jobs',
        'rate_limiting' => 'Redis::throttle() inside job handle() to respect external API limits',
    ],

    // ── Database Performance ───────────────────────────────────────────────────
    'database' => [
        'query_optimization' => [
            'explain'       => 'EXPLAIN ANALYZE on slow queries — look for seq scans on large tables',
            'select_n'      => 'Use select() to limit columns — avoid SELECT *',
            'joins_vs_n1'   => 'Proper JOIN is faster than N+1. Use Eloquent with() or raw join.',
            'chunk_cursor'  => 'chunk(500) or lazy() for batch processing — never ::all() on large sets',
            'count_trick'   => 'withCount() instead of loading collection and count()',
        ],
        'connection_pooling' => 'Use PgBouncer (PostgreSQL) or ProxySQL (MySQL) for high-traffic apps',
        'read_replicas'      => "DB::connection('mysql_read') for reports, ::connection('mysql') for writes",
        'partitioning'       => 'Partition large tables (orders, logs) by date range when > 10M rows',
    ],

    // ── Frontend Performance ───────────────────────────────────────────────────
    'frontend' => [
        'assets'     => 'Vite for bundling. Minify CSS/JS. Hash filenames for cache-busting.',
        'images'     => 'WebP format. Lazy loading. Responsive srcset. Max 200KB for thumbnails.',
        'cdn'        => 'Serve static assets (images, CSS, JS) from CDN in production.',
        'livewire'   => 'Use wire:lazy for below-fold components. Defer non-critical data.',
        'pagination' => 'Cursor pagination for infinite scroll (no COUNT(*) query).',
    ],

    // ── Redis Patterns ────────────────────────────────────────────────────────
    'redis' => [
        'sessions'      => 'SESSION_DRIVER=redis — shared sessions across servers',
        'cache'         => 'CACHE_DRIVER=redis — distributed cache',
        'queues'        => 'QUEUE_CONNECTION=redis — reliable job queue',
        'rate_limiting' => 'RateLimiter::for() using Redis counters',
        'pub_sub'       => 'Real-time events via Redis Pub/Sub + Laravel Reverb/Pusher',
        'atomic_ops'    => 'Redis::set(key, value, EX, 60, NX) for distributed locks',
    ],

    // ── Monitoring & Profiling ─────────────────────────────────────────────────
    'monitoring' => [
        'development' => ['Laravel Debugbar', 'Telescope'],
        'production'  => ['Laravel Horizon (queues)', 'Sentry (errors)', 'New Relic / Datadog'],
        'metrics'     => ['Response time p95 < 200ms', 'DB query time < 50ms', 'Queue lag < 30s'],
        'alerts'      => 'Alert on: error rate spike, queue depth > 1000, memory > 80%, disk > 90%',
    ],

];
