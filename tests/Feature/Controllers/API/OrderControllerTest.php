<?php

use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create(['name' => 'Test Tenant', 'domain' => 'test.test', 'active' => true]);
    app(TenantService::class)->setTenant($this->tenant);

    // Seed lookup tables required by Order FK constraints
    $this->processingStatus = ProcessingStatus::create(['status_name' => 'Pending', 'display_order' => 1, 'is_active' => true]);
    $this->orderStatus = OrderStatus::create(['status_name' => 'Open', 'display_order' => 1, 'is_active' => true]);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    Gate::define('order_access', fn () => true);
    Gate::define('order_create', fn () => true);
    Gate::define('order_show', fn () => true);
    Gate::define('order_edit', fn () => true);
    Gate::define('order_delete', fn () => true);
});

test('can list orders', function () {
    Order::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/orders');

    $response->assertSuccessful();
});

test('can create order', function () {
    $orderData = [
        'customer_id' => $this->customer->id,
        'order_date' => now()->format('Y-m-d'),
        'promised_date' => now()->addDays(2)->format('Y-m-d'),
        'urgent' => false,
        'discount_type' => 'fixed',
        'discount_amount' => 0,
        'tax_rate' => 10,
        'items' => [
            [
                'item_id' => $this->item->id,
                'service_id' => $this->service->id,
                'quantity' => 2,
                'unit_price' => 50.00,
            ],
        ],
    ];

    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/orders', $orderData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('orders', [
        'customer_id' => $this->customer->id,
        'tenant_id' => $this->tenant->id,
    ]);

    $this->assertDatabaseHas('order_items', [
        'item_id' => $this->item->id,
        'service_id' => $this->service->id,
        'quantity' => 2,
    ]);
});

test('can view order', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertSuccessful()
        ->assertJsonFragment([
            'id' => $order->id,
            'order_number' => $order->order_number,
        ]);
});

test('can update order', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $updateData = [
        'notes' => 'Updated order notes',
        'urgent' => true,
    ];

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/orders/{$order->id}", $updateData);

    $response->assertStatus(202);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'notes' => 'Updated order notes',
        'urgent' => true,
    ]);
});

test('can delete order', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->deleteJson("/api/v1/orders/{$order->id}");

    $response->assertNoContent();

    $this->assertSoftDeleted('orders', [
        'id' => $order->id,
    ]);
});

test('validation fails for invalid order data', function () {
    $invalidData = [
        'customer_id' => 99999,
        'order_date' => '',
        'promised_date' => '',
        'items' => [],
    ];

    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/orders', $invalidData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'customer_id',
            'order_date',
            'promised_date',
            'items',
        ]);
});

test('unauthorized user cannot access orders', function () {
    $response = $this->getJson('/api/v1/orders');

    $response->assertUnauthorized();
});
