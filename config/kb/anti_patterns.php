<?php
/**
 * Anti-Pattern Knowledge Base — What Senior Developers REFUSE to write
 * Level 6: Experience Knowledge — learned from failures
 */
return [

    // ── Laravel Anti-Patterns ─────────────────────────────────────────────────
    'laravel' => [

        'fat_controller' => [
            'smell'  => 'Controller method > 30 lines, contains business logic, DB queries, file operations',
            'fix'    => 'Move to Service class. Controller = receive request → call service → return response.',
            'rule'   => 'Controllers should be thin. Services should be testable.',
        ],

        'n_plus_1_queries' => [
            'smell'  => "foreach (\$orders as \$order) { \$order->customer->name }  // 1+N queries",
            'fix'    => "Order::with('customer')->get()  // 2 queries only",
            'rule'   => 'Always eager-load relationships. Use Laravel Debugbar to catch N+1 in dev.',
            'tools'  => ['Laravel Debugbar', 'telescope', 'clockwork'],
        ],

        'missing_authorization' => [
            'smell'  => 'Route::get("/invoice/{id}", fn($id) => Invoice::find($id)) — any user can see any invoice',
            'fix'    => '$this->authorize("view", $invoice) or policy gate check',
            'rule'   => 'EVERY resource access must verify ownership/permission. This causes IDOR attacks.',
        ],

        'hardcoded_credentials' => [
            'smell'  => '$client = new StripeClient("sk_live_abc123") in code',
            'fix'    => 'config("services.stripe.secret") from .env',
            'rule'   => 'NEVER commit secrets. Use .env + GitHub secrets + secrets manager.',
        ],

        'god_model' => [
            'smell'  => 'User model with 50+ methods, 20+ relationships, business logic inside',
            'fix'    => 'Split into traits (HasSubscription, HasPermissions) + dedicated services',
            'rule'   => 'Models = data representation + relationships. Business logic in Services.',
        ],

        'no_form_requests' => [
            'smell'  => 'Validation inside controller action methods',
            'fix'    => 'Dedicated FormRequest class per action',
            'rule'   => 'FormRequests: validation + authorization + prepare-for-validation in one place.',
        ],

        'direct_db_in_controller' => [
            'smell'  => "DB::table('users')->where(...) inside controller",
            'fix'    => 'Repository or Service layer',
            'rule'   => 'Abstract DB access so you can swap drivers and test in isolation.',
        ],

        'missing_transactions' => [
            'smell'  => 'Multiple DB writes without DB::transaction() — partial failures leave corrupt state',
            'fix'    => "DB::transaction(function() { \$order->save(); \$stock->decrement(); \$invoice->create(); })",
            'rule'   => 'ANY operation touching ≥ 2 tables must be wrapped in a transaction.',
        ],

        'not_using_queues' => [
            'smell'  => 'Sending 1000 emails or generating PDF in request lifecycle → request timeout',
            'fix'    => 'Dispatch a Job: SendBulkEmailJob::dispatch($users)',
            'rule'   => 'Anything > 200ms should be a queued job.',
        ],

        'ignoring_indexes' => [
            'smell'  => 'WHERE clause on unindexed column in 1M+ row table',
            'fix'    => "Schema::table('orders', fn(\$t) => \$t->index(['user_id','status','created_at']))",
            'rule'   => 'Every foreign key and every column used in WHERE/ORDER BY must be indexed.',
        ],

        'no_soft_deletes' => [
            'smell'  => 'Permanently deleting records that might be referenced or need audit trail',
            'fix'    => 'Add SoftDeletes trait to model + deleted_at column to migration',
            'rule'   => 'Use SoftDeletes for: users, orders, invoices, products, any business entity.',
        ],

        'boolean_blindness' => [
            'smell'  => "update(['status' => true]) — what does true mean?",
            'fix'    => "update(['status' => 'active']) or constants: Order::STATUS_ACTIVE",
            'rule'   => 'Use string enums/constants for status fields, not bare booleans.',
        ],

        'raw_exception_exposure' => [
            'smell'  => 'Showing full stack trace to end users in production',
            'fix'    => 'APP_DEBUG=false + custom error pages + log to file',
            'rule'   => 'Never expose internal errors to users. Log them, show friendly message.',
        ],
    ],

    // ── Database Anti-Patterns ─────────────────────────────────────────────────
    'database' => [

        'missing_foreign_keys' => [
            'smell'  => 'order_id column with no foreign key constraint → orphan records',
            'fix'    => "\$table->foreignId('order_id')->constrained()->cascadeOnDelete()",
            'rule'   => 'Every _id column must have a foreign key constraint.',
        ],

        'storing_calculated_values' => [
            'smell'  => 'total_price column computed from unit_price * qty but updated manually',
            'fix'    => 'Use database generated column OR compute in accessor',
            'rule'   => 'Avoid storing values derivable from other columns unless for performance/reporting.',
        ],

        'overusing_json_columns' => [
            'smell'  => 'Storing structured data as JSON blob to "avoid migrations"',
            'fix'    => 'Proper normalized tables. JSON only for truly dynamic/schema-less data.',
            'rule'   => 'JSON columns = reporting nightmare and index impossibility.',
        ],

        'missing_timestamps' => [
            'smell'  => 'Tables without created_at/updated_at',
            'fix'    => 'Always use $timestamps = true (default). Add created_by for audit.',
            'rule'   => 'You WILL need to know when records were created. Always include timestamps.',
        ],

        'no_pagination' => [
            'smell'  => "User::all() on a 100K row table",
            'fix'    => "User::paginate(25) or User::cursorPaginate(25)",
            'rule'   => 'NEVER use ::all() on tables that can grow. Always paginate or limit.',
        ],
    ],

    // ── API Anti-Patterns ─────────────────────────────────────────────────────
    'api' => [

        'exposing_all_fields' => [
            'smell'  => 'return User::find($id) — returns password, tokens, PII',
            'fix'    => 'return new UserResource($user) — explicitly define returned fields',
            'rule'   => 'Always use API Resources. Never return raw Eloquent models.',
        ],

        'inconsistent_error_format' => [
            'smell'  => 'Sometimes returns {error: "..."}, sometimes {message: "..."}, sometimes HTTP 200 for errors',
            'fix'    => 'Unified: {success: bool, message: str, data: any, errors: obj}',
            'rule'   => 'Consistent API responses = happy frontend devs.',
        ],

        'no_versioning' => [
            'smell'  => '/api/users — no version prefix',
            'fix'    => '/api/v1/users',
            'rule'   => 'Always version APIs. Breaking change without version = broken clients.',
        ],
    ],

    // ── Architecture Anti-Patterns ─────────────────────────────────────────────
    'architecture' => [

        'premature_microservices' => [
            'smell'  => 'Splitting a new app into 10 microservices before understanding the domain',
            'fix'    => 'Start as monolith/modular-monolith, extract when pain is real',
            'rule'   => '"Microservices are an optimization, not a starting point." — Martin Fowler',
        ],

        'anemic_domain_model' => [
            'smell'  => 'Models are just data bags, all logic in controllers/services',
            'fix'    => 'Put domain behaviors in the model or domain service',
        ],

        'copy_paste_code' => [
            'smell'  => 'Same 20-line validation block copy-pasted in 5 controllers',
            'fix'    => 'Extract FormRequest or Trait',
            'rule'   => 'DRY — if you copy-paste, you will forget to update one copy.',
        ],

        'ignoring_indexes_at_design' => [
            'smell'  => 'Adding indexes as an afterthought when performance degrades',
            'fix'    => 'Design indexes at migration time based on expected query patterns',
            'rule'   => 'Think about how data will be QUERIED when you design tables.',
        ],
    ],

    // ── Security Anti-Patterns ─────────────────────────────────────────────────
    'security' => [
        'trusting_client_data'    => 'Never trust: user_id from request body, role from request, price from cart payload',
        'storing_plain_passwords' => 'md5/sha1 passwords = game over if DB leaked. Use bcrypt/argon2.',
        'verbose_error_messages'  => '"User not found" vs "Invalid credentials" — the latter reveals valid usernames',
        'open_redirects'          => 'Never redirect to $request->get("redirect_to") without allowlist validation',
        'mass_assignment'         => '$user->fill($request->all()) — attacker sets is_admin=1',
    ],

];
