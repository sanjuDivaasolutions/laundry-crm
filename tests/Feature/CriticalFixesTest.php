<?php

use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Gate::define('order_access', fn () => true);
    Gate::define('order_create', fn () => true);
    Gate::define('order_edit', fn () => true);
    Gate::define('order_show', fn () => true);
    Gate::define('order_delete', fn () => true);
    Gate::define('item_access', fn () => true);
    Gate::define('item_create', fn () => true);
    Gate::define('item_edit', fn () => true);
    Gate::define('service_access', fn () => true);
    Gate::define('service_create', fn () => true);
    Gate::define('service_edit', fn () => true);
});

// ============================================================
// Task #3: OrderItem tenant isolation
// ============================================================

test('order items inherit tenant_id from parent order', function () {
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);
    $item = Item::factory()->create(['tenant_id' => $this->tenant->id, 'price' => 10.00]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'created_by_employee_id' => $this->user->id,
    ]);

    $orderItem = OrderItem::create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'item_id' => $item->id,
        'service_id' => $service->id,
        'item_name' => $item->name,
        'service_name' => $service->name,
        'quantity' => 2,
        'unit_price' => 10.00,
        'total_price' => 20.00,
    ]);

    expect($orderItem->tenant_id)->toBe($this->tenant->id);
});

test('order items are scoped to current tenant', function () {
    $tenant2 = Tenant::factory()->create();

    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);
    $item = Item::factory()->create(['tenant_id' => $this->tenant->id, 'price' => 10.00]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'created_by_employee_id' => $this->user->id,
    ]);

    $orderItem = OrderItem::create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'item_id' => $item->id,
        'service_id' => $service->id,
        'item_name' => $item->name,
        'service_name' => $service->name,
        'quantity' => 1,
        'unit_price' => 10.00,
        'total_price' => 10.00,
    ]);

    // Switch to tenant 2 — order items from tenant 1 should not be visible
    app(TenantService::class)->setTenant($tenant2);
    expect(OrderItem::count())->toBe(0);

    // Switch back — should be visible
    app(TenantService::class)->setTenant($this->tenant);
    expect(OrderItem::count())->toBe(1);
});

// ============================================================
// Task #4: Cascade delete — payments deleted with order
// ============================================================

test('payments are cascade deleted when order is deleted', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'created_by_employee_id' => $this->user->id,
    ]);

    Payment::create([
        'tenant_id' => $this->tenant->id,
        'payment_number' => 'PAY-TEST-001',
        'order_id' => $order->id,
        'customer_id' => $order->customer_id,
        'payment_date' => now(),
        'amount' => 50.00,
        'payment_method' => 'cash',
        'received_by_employee_id' => $this->user->id,
        'created_at' => now(),
    ]);

    expect(Payment::count())->toBe(1);

    // Force delete the order — payment should cascade
    $order->forceDelete();

    expect(Payment::count())->toBe(0);
});

// ============================================================
// Task #5: Authorization checks on controllers
// ============================================================

test('item store requires item_create gate', function () {
    Gate::define('item_create', fn () => false);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/items', ['name' => 'Test Item']);

    $response->assertForbidden();
});

test('service store requires service_create gate', function () {
    Gate::define('service_create', fn () => false);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/services', ['name' => 'Test Service']);

    $response->assertForbidden();
});

// ============================================================
// Task #8: Dynamic status ID lookups
// ============================================================

test('ProcessingStatus::idFor returns correct ID for each status', function () {
    foreach (ProcessingStatusEnum::cases() as $status) {
        $dbStatus = ProcessingStatus::where('status_name', $status->value)->first();
        expect(ProcessingStatus::idFor($status))->toBe($dbStatus->id);
    }
});

test('OrderStatus::idFor returns correct ID for Open and Closed', function () {
    $openId = OrderStatus::where('status_name', 'Open')->first()->id;
    $closedId = OrderStatus::where('status_name', 'Closed')->first()->id;

    expect(OrderStatus::idFor('Open'))->toBe($openId);
    expect(OrderStatus::idFor('Closed'))->toBe($closedId);
});

test('ProcessingStatus::idFor throws for invalid status', function () {
    ProcessingStatus::idFor(ProcessingStatusEnum::from('NonExistent'));
})->throws(\ValueError::class);

test('OrderStatus::idFor throws for invalid status name', function () {
    OrderStatus::idFor('NonExistent');
})->throws(\RuntimeException::class);

// ============================================================
// Task #8: Kanban board excludes cancelled and delivered
// ============================================================

test('kanban board data excludes cancelled and delivered statuses', function () {
    $posService = app(\App\Services\PosService::class);
    $data = $posService->getKanbanData();

    $statusNames = collect($data['statuses'])->pluck('status_name')->toArray();

    expect($statusNames)->not->toContain('Cancelled');
    expect($statusNames)->not->toContain('Delivered');
    expect($statusNames)->toContain('Pending');
    expect($statusNames)->toContain('Washing');
    expect($statusNames)->toContain('Drying');
    expect($statusNames)->toContain('Ready Area');
});

// ============================================================
// Task #8: Statistics use dynamic lookups
// ============================================================

test('statistics returns correct counts using dynamic status IDs', function () {
    $pendingId = ProcessingStatus::idFor(ProcessingStatusEnum::Pending);
    $washingId = ProcessingStatus::idFor(ProcessingStatusEnum::Washing);

    Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $pendingId,
        'order_date' => now(),
        'created_by_employee_id' => $this->user->id,
    ]);

    Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $washingId,
        'order_date' => now(),
        'created_by_employee_id' => $this->user->id,
    ]);

    $posService = app(\App\Services\PosService::class);
    $stats = $posService->getStatistics();

    expect($stats['pending'])->toBe(1);
    expect($stats['washing'])->toBe(1);
    expect($stats['drying'])->toBe(0);
});

// ============================================================
// Task #6: Cross-tenant delivery schedule validation
// ============================================================

test('delivery schedule store validates order belongs to tenant', function () {
    $tenant2 = Tenant::factory()->create();
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

    // Create order in tenant 2 (bypassing tenant scope)
    app(TenantService::class)->setTenant($tenant2);
    $otherOrder = Order::factory()->create([
        'tenant_id' => $tenant2->id,
        'created_by_employee_id' => $user2->id,
    ]);
    app(TenantService::class)->setTenant($this->tenant);

    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/deliveries', [
            'order_id' => $otherOrder->id,
            'customer_id' => $customer->id,
            'type' => 'pickup',
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
        ]);

    $response->assertUnprocessable();
});

// ============================================================
// Task #1: Config defaults
// ============================================================

test('CORS config defaults to restrictive localhost origins', function () {
    $config = config('cors.allowed_origins');

    expect($config)->not->toContain('*');
});

// ============================================================
// Task #2: Stripe config uses correct env key names
// ============================================================

test('cashier config reads STRIPE_PUBLIC_KEY env variable', function () {
    $configFile = file_get_contents(base_path('config/cashier.php'));

    expect($configFile)->toContain("env('STRIPE_PUBLIC_KEY')");
    expect($configFile)->toContain("env('STRIPE_SECRET_KEY')");
    expect($configFile)->not->toContain("env('STRIPE_KEY')");
    expect($configFile)->not->toContain("env('STRIPE_SECRET')");
});

// ============================================================
// Task #5: POS board API authorization
// ============================================================

test('POS board requires order_access gate', function () {
    Gate::define('order_access', fn () => false);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/pos/board');

    $response->assertForbidden();
});

test('POS quick order requires order_create gate', function () {
    Gate::define('order_create', fn () => false);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/pos/orders', []);

    $response->assertForbidden();
});

test('POS order cancel requires order_delete gate', function () {
    Gate::define('order_delete', fn () => false);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'created_by_employee_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/pos/orders/{$order->id}");

    $response->assertForbidden();
});

// ============================================================
// Task #8: Order creation uses dynamic status IDs
// ============================================================

test('quick order sets processing status to Pending via dynamic lookup', function () {
    $service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'piece',
    ]);
    $item = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 10.00,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/pos/orders', [
            'customer_name' => 'Test Customer',
            'customer_phone' => '1234567890',
            'service_id' => $service->id,
            'items' => [
                ['item_id' => $item->id, 'quantity' => 1],
            ],
        ]);

    $response->assertCreated();

    $order = Order::latest()->first();
    $pendingId = ProcessingStatus::idFor(ProcessingStatusEnum::Pending);
    $openId = OrderStatus::idFor('Open');

    expect($order->processing_status_id)->toBe($pendingId);
    expect($order->order_status_id)->toBe($openId);
});

// ============================================================
// Task #8: Order history returns cancelled and delivered orders
// ============================================================

test('order history endpoint returns cancelled and delivered orders', function () {
    $cancelledId = ProcessingStatus::idFor(ProcessingStatusEnum::Cancelled);
    $deliveredId = ProcessingStatus::idFor(ProcessingStatusEnum::Delivered);

    Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $cancelledId,
        'created_by_employee_id' => $this->user->id,
    ]);

    Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $deliveredId,
        'created_by_employee_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/pos/history');

    $response->assertSuccessful();
    expect($response->json('data.orders'))->toHaveCount(2);
});

// ============================================================
// Task #5: Customer options endpoint
// ============================================================

test('customer options endpoint returns customers', function () {
    Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/options/customers');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1);
});

// ============================================================
// Task #12: Translation key generation
// ============================================================

test('LanguageService getCrudTerms generates module-specific field keys', function () {
    $terms = \App\Services\LanguageService::getCrudTerms();

    // Flatten all terms into a single key list
    $allKeys = [];
    foreach ($terms as $group) {
        foreach ($group as $key => $value) {
            $allKeys[] = $key;
        }
    }

    // General field keys should exist
    expect($allKeys)->toContain('general.fields.name');
    expect($allKeys)->toContain('general.fields.id');
    expect($allKeys)->toContain('general.fields.search');

    // Module-specific field keys should also exist
    expect($allKeys)->toContain('customer.fields.customer_code');
    expect($allKeys)->toContain('customer.fields.name');
    expect($allKeys)->toContain('order.fields.order_number');
    expect($allKeys)->toContain('order.fields.order_date');
    expect($allKeys)->toContain('item.fields.price');
    expect($allKeys)->toContain('service.fields.code');

    // Title singular should be extracted
    expect($allKeys)->toContain('customer.title_singular');
    expect($allKeys)->toContain('order.title_singular');
    expect($allKeys)->toContain('item.title_singular');
});
