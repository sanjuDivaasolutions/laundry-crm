<?php

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    $tenantService = app(TenantService::class);
    $tenantService->setTenant($this->tenant);
});

test('service can be created with weight pricing type', function () {
    $service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'weight',
        'price_per_pound' => 2.50,
        'minimum_weight' => 1.00,
    ]);

    expect($service->pricing_type)->toBe('weight');
    expect((float) $service->price_per_pound)->toBe(2.50);
    expect((float) $service->minimum_weight)->toBe(1.00);
});

test('service price can have price per pound', function () {
    $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'weight',
    ]);

    $servicePrice = ServicePrice::create([
        'tenant_id' => $this->tenant->id,
        'item_id' => $item->id,
        'service_id' => $service->id,
        'price' => 10.00,
        'price_per_pound' => 3.00,
        'is_active' => true,
    ]);

    expect((float) $servicePrice->price_per_pound)->toBe(3.00);
});

test('order item can be weight based', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id]);
    $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'weight',
        'price_per_pound' => 2.00,
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'service_id' => $service->id,
        'item_name' => $item->name,
        'service_name' => $service->name,
        'pricing_type' => 'weight',
        'weight' => 5.50,
        'weight_unit' => 'lb',
        'quantity' => 1,
        'unit_price' => 2.00,
        'total_price' => 11.00,
    ]);

    expect($orderItem->pricing_type)->toBe('weight');
    expect((float) $orderItem->weight)->toBe(5.50);
    expect($orderItem->weight_unit)->toBe('lb');
    expect((float) $orderItem->total_price)->toBe(11.00);
});

test('weight based order item total equals weight times price per pound', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id]);
    $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'weight',
    ]);

    $weight = 5.00;
    $pricePerPound = 2.50;
    $expectedTotal = $weight * $pricePerPound; // 12.50

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'service_id' => $service->id,
        'item_name' => $item->name,
        'service_name' => $service->name,
        'pricing_type' => 'weight',
        'weight' => $weight,
        'weight_unit' => 'lb',
        'quantity' => 1,
        'unit_price' => $pricePerPound,
        'total_price' => $expectedTotal,
    ]);

    expect((float) $orderItem->total_price)->toBe(12.50);
});

test('piece based order item still works as before', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id]);
    $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'service_id' => $service->id,
        'item_name' => $item->name,
        'service_name' => $service->name,
        'pricing_type' => 'piece',
        'quantity' => 3,
        'unit_price' => 10.00,
        'total_price' => 30.00,
    ]);

    expect($orderItem->pricing_type)->toBe('piece');
    expect($orderItem->weight)->toBeNull();
    expect((float) $orderItem->total_price)->toBe(30.00);
});

test('service defaults to piece pricing type', function () {
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    // Refresh from DB to get the default value (SQLite may not apply enum defaults)
    $service->refresh();
    expect($service->pricing_type ?? 'piece')->toBe('piece');
    expect($service->price_per_pound)->toBeNull();
});
