<?php
/**
 * Testing Knowledge Base
 * Senior developers write tests. AI should generate tests automatically.
 */
return [

    // ── Testing Pyramid ────────────────────────────────────────────────────────
    'pyramid' => [
        'unit'        => ['70% of tests', 'Fast', 'Test pure functions, services, calculations'],
        'feature'     => ['20% of tests', 'Medium speed', 'Test HTTP endpoints end-to-end with fake DB'],
        'integration' => ['5% of tests', 'Slow', 'Test real external services (with sandbox credentials)'],
        'e2e'         => ['5% of tests', 'Slowest', 'Browser tests for critical user journeys (Dusk)'],
    ],

    // ── Laravel Test Patterns ──────────────────────────────────────────────────
    'laravel_patterns' => [

        'feature_test_template' => [
            'description' => 'HTTP endpoint test with auth and database',
            'template' => <<<'PHP'
public function test_user_can_create_order(): void
{
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'stock' => 10]);

    $response = $this->actingAs($user)->postJson('/api/v1/orders', [
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        'address_id' => Address::factory()->for($user)->create()->id,
    ]);

    $response->assertCreated()->assertJsonStructure(['data' => ['id', 'total', 'status']]);
    $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'status' => 'pending']);
    $this->assertEquals(8, $product->fresh()->stock); // stock decremented
}
PHP,
        ],

        'policy_test_template' => [
            'description' => 'Authorization policy test',
            'template' => <<<'PHP'
public function test_user_cannot_view_another_users_order(): void
{
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->create();

    $this->actingAs($other)->getJson("/api/v1/orders/{$order->id}")
         ->assertForbidden();
}
PHP,
        ],

        'validation_test_template' => [
            'description' => 'Form request validation test',
            'template' => <<<'PHP'
public function test_order_requires_valid_items(): void
{
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/v1/orders', ['items' => []])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['items']);
}
PHP,
        ],

        'service_unit_test' => [
            'description' => 'Service class unit test with mock',
            'template' => <<<'PHP'
public function test_order_service_calculates_total_correctly(): void
{
    $service = new OrderService();
    $items = [
        ['price' => 100, 'quantity' => 2],
        ['price' => 50,  'quantity' => 1],
    ];
    $this->assertEquals(250, $service->calculateTotal($items));
}
PHP,
        ],
    ],

    // ── Must-Test Scenarios ────────────────────────────────────────────────────
    'must_test' => [
        'authentication' => [
            'Login with valid credentials → 200 + token',
            'Login with invalid password → 401',
            'Login throttle → 429 after 5 attempts',
            'Access protected route without token → 401',
        ],
        'authorization' => [
            'Owner can update own resource',
            'Other user cannot update resource they do not own',
            'Admin can manage all resources',
            'Soft-deleted record returns 404',
        ],
        'validation' => [
            'Required fields missing → 422 with field errors',
            'Invalid email format → 422',
            'String in numeric field → 422',
            'Future date for "past" field → 422',
        ],
        'business_logic' => [
            'Payment confirms order status changes',
            'Stock decrements on order',
            'Refund restores stock',
            'Duplicate submission prevented',
        ],
    ],

    // ── Test Database Strategy ────────────────────────────────────────────────
    'database_strategy' => [
        'rule'            => 'Use RefreshDatabase trait (fresh DB per test) in feature tests',
        'factories'       => 'Create factories for every model. Use states for variations.',
        'seeders_in_test' => 'Run only essential seeders (permissions, countries, currencies)',
        'transactions'    => 'DatabaseTransactions trait is faster but incompatible with certain tests',
    ],

    // ── Testing Tools ──────────────────────────────────────────────────────────
    'tools' => [
        'phpunit'   => 'Default Laravel test runner',
        'pest'      => 'Elegant syntax, recommended for new projects: it("does X", fn() => ...)',
        'mockery'   => 'Mock external services: Mockery::mock(StripeGateway::class)',
        'faker'     => 'Built into factories: fake()->email(), fake()->name()',
        'http_fake' => 'Http::fake() for mocking external HTTP calls in tests',
        'queue_fake'=> 'Queue::fake() — assert jobs dispatched without actually running',
        'mail_fake' => 'Mail::fake() — assert emails sent without delivery',
        'storage_fake' => 'Storage::fake() — test file uploads without real disk',
    ],

    // ── CI Integration ─────────────────────────────────────────────────────────
    'ci_integration' => [
        'github_actions' => 'Run on every push. Fail PR if tests fail.',
        'coverage'       => 'Aim for 70%+ coverage on Services and critical paths',
        'speed'          => 'Parallel test execution: php artisan test --parallel',
        'reporting'      => 'Generate coverage report and upload to Codecov',
    ],

];
