<?php

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed statuses first as they are needed by factories
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(\App\Services\TenantService::class)->setTenant($this->tenant);
});

test('order remains open through washing drying and ready stages', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $openStatus = OrderStatus::where('status_name', OrderStatusEnum::Open->value)->first();
    $washingStage = ProcessingStatus::where('status_name', ProcessingStatusEnum::Washing->value)->first();
    $dryingStage = ProcessingStatus::where('status_name', ProcessingStatusEnum::Drying->value)->first();
    $readyStage = ProcessingStatus::where('status_name', ProcessingStatusEnum::Ready->value)->first();

    // Initial state
    expect($order->order_status_id)->toBe($openStatus->id);
    expect($order->processingStatus->status_name)->toBe(ProcessingStatusEnum::Pending->value);

    // Move to Washing
    $order->update(['processing_status_id' => $washingStage->id]);
    expect($order->order_status_id)->toBe($openStatus->id);
    expect($order->refresh()->processingStatus->status_name)->toBe(ProcessingStatusEnum::Washing->value);

    // Move to Drying
    $order->update(['processing_status_id' => $dryingStage->id]);
    expect($order->order_status_id)->toBe($openStatus->id);
    expect($order->refresh()->processingStatus->status_name)->toBe(ProcessingStatusEnum::Drying->value);

    // Move to Ready Area
    $order->update(['processing_status_id' => $readyStage->id, 'hanger_number' => 'H-101']);
    expect($order->order_status_id)->toBe($openStatus->id);
    expect($order->refresh()->processingStatus->status_name)->toBe(ProcessingStatusEnum::Ready->value);
    expect($order->hanger_number)->toBe('H-101');
});

test('order closes only upon pickup', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'payment_status' => PaymentStatusEnum::Paid, // Prepaid
    ]);

    $readyStage = ProcessingStatus::where('status_name', ProcessingStatusEnum::Ready->value)->first();
    $order->update(['processing_status_id' => $readyStage->id]);

    // Prepaid and Ready, but still Open until pickup
    expect($order->refresh()->order_status_id)->toBe(OrderStatus::where('status_name', OrderStatusEnum::Open->value)->first()->id);
    expect($order->closed_at)->toBeNull();

    // Pickup occurs
    $order->markAsPickedUp();

    expect($order->refresh()->order_status_id)->toBe(OrderStatus::where('status_name', OrderStatusEnum::Closed->value)->first()->id);
    expect($order->processingStatus->status_name)->toBe(ProcessingStatusEnum::Delivered->value);
    expect($order->picked_up_at)->not->toBeNull();
    expect($order->closed_at)->not->toBeNull();
});

test('order items capture garment details and services', function () {
    $order = Order::factory()->create(['tenant_id' => $this->tenant->id]);
    $laundryItem = Item::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'item_id' => $laundryItem->id,
        'service_id' => $service->id,
        'color' => 'Blue',
        'brand' => 'Levis',
        'defect_notes' => 'Small stain on left sleeve',
    ]);

    expect($orderItem->color)->toBe('Blue');
    expect($orderItem->brand)->toBe('Levis');
    expect($orderItem->defect_notes)->toBe('Small stain on left sleeve');
    expect($orderItem->item)->toBeInstanceOf(Item::class);
    expect($orderItem->service)->toBeInstanceOf(Service::class);
});
