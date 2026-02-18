<?php

use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
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

    // Define gates for POS access
    Gate::define('order_access', fn () => true);
    Gate::define('order_create', fn () => true);
    Gate::define('order_edit', fn () => true);
    Gate::define('order_show', fn () => true);
    Gate::define('order_delete', fn () => true);

    // Create a service and items for testing
    $this->service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'piece',
    ]);

    $this->item1 = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 10.00,
    ]);

    $this->item2 = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 15.00,
    ]);

    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
});

function createOrderWithItems($test, int $processingStatusId = 2, float $paidAmount = 0): Order
{
    $order = Order::factory()->create([
        'tenant_id' => $test->tenant->id,
        'customer_id' => $test->customer->id,
        'processing_status_id' => $processingStatusId,
        'subtotal' => 20.00,
        'total_amount' => 20.00,
        'paid_amount' => $paidAmount,
        'balance_amount' => 20.00 - $paidAmount,
        'payment_status' => $paidAmount > 0 ? PaymentStatusEnum::Partial : PaymentStatusEnum::Unpaid,
        'created_by_employee_id' => $test->user->id,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'item_id' => $test->item1->id,
        'service_id' => $test->service->id,
        'item_name' => $test->item1->name,
        'service_name' => $test->service->name,
        'pricing_type' => 'piece',
        'quantity' => 2,
        'unit_price' => 10.00,
        'total_price' => 20.00,
    ]);

    return $order;
}

test('can update order when Pending (status 2)', function () {
    $order = createOrderWithItems($this, 2);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 3],
            ],
            'urgent' => true,
            'notes' => 'Updated notes',
        ]);

    $response->assertSuccessful();
    expect($order->fresh()->urgent)->toBeTrue();
    expect($order->fresh()->notes)->toBe('Updated notes');
});

test('can update order when Washing (status 3)', function () {
    $order = createOrderWithItems($this, 3);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 1],
                ['item_id' => $this->item2->id, 'quantity' => 2],
            ],
        ]);

    $response->assertSuccessful();
});

test('cannot update order when Drying (status 4)', function () {
    $order = createOrderWithItems($this, 4);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 1],
            ],
        ]);

    $response->assertStatus(422);
});

test('cannot update order when Ready (status 5)', function () {
    $order = createOrderWithItems($this, 5);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 1],
            ],
        ]);

    $response->assertStatus(422);
});

test('cannot update order when payment has been made', function () {
    $order = createOrderWithItems($this, 2, 5.00);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 1],
            ],
        ]);

    $response->assertStatus(422);
});

test('totals are recalculated after update', function () {
    $order = createOrderWithItems($this, 2);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 3], // 3 × $10 = $30
                ['item_id' => $this->item2->id, 'quantity' => 1], // 1 × $15 = $15
            ],
        ]);

    $response->assertSuccessful();

    $fresh = $order->fresh();
    expect((float) $fresh->subtotal)->toBe(45.00);
    expect((float) $fresh->total_amount)->toBe(45.00);
    expect((float) $fresh->balance_amount)->toBe(45.00);
});

test('validation fails with empty items', function () {
    $order = createOrderWithItems($this, 2);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [],
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['items']);
});

test('unauthorized user cannot update order', function () {
    Gate::define('order_edit', fn () => false);

    $order = createOrderWithItems($this, 2);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item1->id, 'quantity' => 1],
            ],
        ]);

    $response->assertForbidden();
});

test('old items are soft-deleted on update', function () {
    $order = createOrderWithItems($this, 2);

    $oldItemCount = OrderItem::where('order_id', $order->id)->count();
    expect($oldItemCount)->toBe(1);

    $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/pos/orders/{$order->id}", [
            'items' => [
                ['item_id' => $this->item2->id, 'quantity' => 2],
            ],
        ]);

    // Old items are soft-deleted
    $trashedCount = OrderItem::withTrashed()
        ->where('order_id', $order->id)
        ->whereNotNull('deleted_at')
        ->count();
    expect($trashedCount)->toBe(1);

    // New items exist
    $activeCount = OrderItem::where('order_id', $order->id)->count();
    expect($activeCount)->toBe(1);
});
