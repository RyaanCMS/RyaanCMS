<?php
/**
 * Coding Standards Knowledge Base
 * SOLID, DRY, KISS, Laravel conventions, PSR-12
 * Level 1: Code Knowledge — the foundation
 */
return [

    // ── SOLID Principles ─────────────────────────────────────────────────────
    'solid' => [
        'S_single_responsibility' => [
            'rule'    => 'One class = one reason to change',
            'laravel' => 'UserController handles HTTP only. UserService handles business logic. UserRepository handles data.',
            'smell'   => 'Controller that sends emails, processes payment, and updates inventory',
        ],
        'O_open_closed' => [
            'rule'    => 'Open for extension, closed for modification',
            'laravel' => 'Use interfaces + dependency injection. Add new payment provider without changing PaymentService.',
            'smell'   => 'if($provider === "stripe") {...} elseif($provider === "paypal") {...} in service class',
        ],
        'L_liskov_substitution' => [
            'rule'    => 'Subclasses must be substitutable for their base types',
            'laravel' => 'All payment drivers implement PaymentDriverInterface with same method signatures',
        ],
        'I_interface_segregation' => [
            'rule'    => 'Clients should not depend on interfaces they do not use',
            'laravel' => 'Separate: HasOrders, HasSubscription, HasProfile — not one giant UserInterface',
        ],
        'D_dependency_inversion' => [
            'rule'    => 'Depend on abstractions, not concretions',
            'laravel' => 'Inject PaymentGatewayInterface in constructor, not StripeGateway directly',
            'how'     => 'Register binding in AppServiceProvider: app()->bind(Interface::class, Concrete::class)',
        ],
    ],

    // ── DRY Principle ─────────────────────────────────────────────────────────
    'dry' => [
        'rule'  => "Don't Repeat Yourself — if you write it twice, extract it",
        'tools' => ['Traits for shared model behavior', 'Base Controller for common responses',
                    'Form Requests for validation', 'Scopes for common query conditions',
                    'API Resources for transformation', 'Helper classes for utility functions'],
    ],

    // ── KISS Principle ────────────────────────────────────────────────────────
    'kiss' => [
        'rule'  => 'Keep It Simple. Complex code = expensive maintenance.',
        'signs' => ['Method > 20 lines → extract', 'Nesting > 3 levels → extract or early return',
                    'Method > 3 parameters → use DTO or array', 'Class > 300 lines → split'],
    ],

    // ── Laravel-Specific Conventions ──────────────────────────────────────────
    'laravel_conventions' => [

        'naming' => [
            'controllers'   => 'PascalCase, singular, suffix: UserController',
            'models'        => 'PascalCase, singular: User, OrderItem',
            'migrations'    => 'snake_case, descriptive: 2024_01_01_create_users_table',
            'tables'        => 'snake_case, plural: users, order_items, product_categories',
            'columns'       => 'snake_case: first_name, created_at, is_active',
            'routes'        => 'kebab-case: /user-profiles, /order-items',
            'route_names'   => 'dot notation: users.index, orders.store, invoices.download',
            'views'         => 'kebab-case folder/file: users/edit.blade.php',
            'jobs'          => 'Verb + Noun: SendInvoiceEmailJob, ProcessPaymentJob',
            'events'        => 'Past tense: OrderPlaced, PaymentReceived, UserRegistered',
            'listeners'     => 'Verb: SendOrderConfirmation, UpdateInventory',
            'policies'      => 'Model name + Policy: OrderPolicy, InvoicePolicy',
            'form_requests' => 'Verb + Model + Request: StoreOrderRequest, UpdateUserRequest',
        ],

        'response_format' => [
            'success_200'  => "return response()->json(['success' => true, 'data' => \$resource, 'message' => 'Created'])",
            'error_422'    => "return response()->json(['success' => false, 'errors' => \$validator->errors()], 422)",
            'error_404'    => "return response()->json(['success' => false, 'message' => 'Not found'], 404)",
            'error_403'    => "return response()->json(['success' => false, 'message' => 'Unauthorized'], 403)",
            'paginated'    => "return UserResource::collection(\$users->paginate(25))",
        ],

        'eloquent_best_practices' => [
            'scopes'            => 'Use local scopes for reusable query conditions: scopeActive(), scopeByTenant()',
            'accessors'         => 'Use for computed properties: getFullNameAttribute()',
            'mutators'          => 'Use for data normalization on save: setEmailAttribute() → strtolower',
            'casts'             => "Cast JSON → array, boolean → bool, dates → Carbon in \$casts array",
            'observers'         => 'Use for side effects on model events: OrderObserver::created() → generate invoice',
            'eager_loading'     => "with('relationship') always — NEVER lazy-load in loops",
            'chunking'          => "chunk(500) or cursor() for processing large datasets — never ::all()",
        ],

        'service_layer' => [
            'structure' => 'app/Services/{Domain}/{FeatureName}Service.php',
            'pattern'   => 'Thin controller → inject service → service returns value or throws exception',
            'example'   => "class OrderService { public function place(User \$user, array \$data): Order }",
        ],

        'repository_pattern' => [
            'when'    => 'Complex query logic, multiple data sources, need to mock in tests',
            'skip_when' => 'Simple CRUD — Eloquent is the repository',
            'structure' => "interface OrderRepositoryInterface\nclass EloquentOrderRepository implements OrderRepositoryInterface",
        ],
    ],

    // ── PSR Standards ─────────────────────────────────────────────────────────
    'psr' => [
        'PSR-1' => 'Basic coding standard: <?php tag, UTF-8 no BOM, PascalCase classes, camelCase methods',
        'PSR-4' => 'Autoloading: App\\Services\\UserService → app/Services/UserService.php',
        'PSR-12'=> 'Extended coding style: 4-space indents, opening brace on same line for methods',
    ],

    // ── Error Handling ────────────────────────────────────────────────────────
    'error_handling' => [
        'rule_1'  => 'Use specific exception classes: OrderNotFoundException, PaymentFailedException',
        'rule_2'  => 'Catch specific exceptions, not generic Exception',
        'rule_3'  => 'Log errors with context: Log::error("Payment failed", [context array])',
        'rule_4'  => 'Never swallow exceptions silently: catch(){} is a bug',
        'rule_5'  => 'Use Handler.php to transform exceptions into API responses centrally',
    ],

    // ── Code Organization ─────────────────────────────────────────────────────
    'organization' => [
        'app_structure' => [
            'Http/Controllers'     => 'Thin, handle HTTP only',
            'Http/Requests'        => 'Validation and authorization',
            'Http/Resources'       => 'API response transformation',
            'Http/Middleware'      => 'Cross-cutting concerns',
            'Services'             => 'Business logic',
            'Repositories'         => 'Data access abstraction (when needed)',
            'Events + Listeners'   => 'Domain events and side effects',
            'Jobs'                 => 'Async/background processing',
            'Notifications'        => 'User notifications (email, SMS, push)',
            'Policies'             => 'Authorization rules per model',
            'Observers'            => 'Model lifecycle hooks',
            'DTOs'                 => 'Data Transfer Objects for complex inputs',
            'Enums'                => 'PHP 8.1+ enums for status/type constants',
        ],
    ],

];
