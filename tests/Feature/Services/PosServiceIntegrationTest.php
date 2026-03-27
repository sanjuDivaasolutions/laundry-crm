<?php

use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PosService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    $this->service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'piece',
    ]);

    $this->item1 = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 25.00,
    ]);

    $this->item2 = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 15.00,
    ]);

    ServicePrice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'item_id' => $this->item1->id,
        'service_id' => $this->service->id,
        'price' => 30.00,
    ]);

    ServicePrice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'item_id' => $this->item2->id,
        'service_id' => $this->service->id,
        'price' => 20.00,
    ]);

    $this->posService = app(PosService::class);
});

it('creates a quick order with correct totals and customer', function () {
    $data = [
        'customer_name' => 'John Doe',
        'customer_phone' => '555-1234',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item1->id, 'quantity' => 2],
            ['item_id' => $this->item2->id, 'quantity' => 1],
        ],
    ];

    $order = $this->posService->createQuickOrder($data);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->order_number)->toStartWith('ORD');
    expect((float) $order->total_amount)->toBe(80.00);
    expect((float) $order->balance_amount)->toBe(80.00);
    expect($order->payment_status)->toBe(PaymentStatusEnum::Unpaid);
    expect($order->processingStatus->status_name)->toBe(ProcessingStatusEnum::Pending->value);

    $customer = Customer::where('phone', '555-1234')->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('John Doe');
    expect($order->customer_id)->toBe($customer->id);

    expect($order->orderItems)->toHaveCount(2);
});

it('processes payment and completes the full order lifecycle', function () {
    $order = $this->posService->createQuickOrder([
        'customer_name' => 'Jane Smith',
        'customer_phone' => '555-5678',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item1->id, 'quantity' => 1],
        ],
    ]);

    expect((float) $order->total_amount)->toBe(30.00);

    $result = $this->posService->processPayment($order, [
        'amount' => 30.00,
        'payment_method' => 'cash',
    ]);

    $paidOrder = $result['order'];
    $payment = $result['payment'];

    expect($paidOrder->payment_status)->toBe(PaymentStatusEnum::Paid);
    expect((float) $paidOrder->balance_amount)->toBe(0.00);
    expect($paidOrder->processingStatus->status_name)->toBe(ProcessingStatusEnum::Delivered->value);
    expect($paidOrder->closed_at)->not->toBeNull();

    expect($payment->payment_number)->toStartWith('PAY');
    expect((float) $payment->amount)->toBe(30.00);

    $customer = Customer::where('phone', '555-5678')->first();
    expect($customer->loyalty_points)->toBeGreaterThan(0);
    expect($customer->total_orders_count)->toBe(1);
    expect((float) $customer->total_spent)->toBe(30.00);
});

it('finds existing customer by phone on subsequent orders', function () {
    $this->posService->createQuickOrder([
        'customer_name' => 'Repeat Customer',
        'customer_phone' => '555-9999',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item1->id, 'quantity' => 1],
        ],
    ]);

    $customerCount = Customer::where('phone', '555-9999')->count();
    expect($customerCount)->toBe(1);

    $order2 = $this->posService->createQuickOrder([
        'customer_name' => 'Repeat Customer',
        'customer_phone' => '555-9999',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item2->id, 'quantity' => 1],
        ],
    ]);

    $customerCount = Customer::where('phone', '555-9999')->count();
    expect($customerCount)->toBe(1);

    $customer = Customer::where('phone', '555-9999')->first();
    expect($order2->customer_id)->toBe($customer->id);
});
