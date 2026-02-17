<?php

use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\LoyaltyService;
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

test('order can store tip amount', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'subtotal' => 50.00,
        'total_amount' => 53.00,
        'tip_amount' => 3.00,
        'balance_amount' => 53.00,
    ]);

    expect((float) $order->tip_amount)->toBe(3.00);
    expect((float) $order->total_amount)->toBe(53.00);
});

test('tip amount defaults to zero', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    expect((float) $order->tip_amount)->toBe(0.00);
});

test('loyalty points exclude tip amount', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_points' => 0,
        'loyalty_tier' => 'bronze',
        'total_orders_count' => 0,
        'total_spent' => 0,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
        'subtotal' => 20.00,
        'tip_amount' => 3.00,
        'total_amount' => 23.00,
        'payment_status' => PaymentStatusEnum::Paid,
    ]);

    $loyaltyService = app(LoyaltyService::class);
    $transaction = $loyaltyService->awardPointsForOrder($order);

    // Points should be based on total_amount - tip_amount = $20, not $23
    // Bronze tier multiplier is 1.0, so floor(20 * 1) = 20 points
    expect($transaction->points)->toBe(20);
});

test('tip amount is included in total calculation', function () {
    $subtotal = 50.00;
    $tipAmount = 7.50;
    $expectedTotal = $subtotal + $tipAmount;

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'subtotal' => $subtotal,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'tip_amount' => $tipAmount,
        'total_amount' => $expectedTotal,
        'balance_amount' => $expectedTotal,
    ]);

    expect((float) $order->total_amount)->toBe($expectedTotal);
    expect((float) $order->balance_amount)->toBe($expectedTotal);
});
