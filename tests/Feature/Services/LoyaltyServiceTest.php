<?php

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
    app(TenantService::class)->setTenant($this->tenant);

    $this->loyaltyService = app(LoyaltyService::class);
});

it('does not award points to soft-deleted customer', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_tier' => 'bronze',
        'loyalty_points' => 0,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
        'total_amount' => 100.00,
        'tip_amount' => 0,
    ]);

    // Soft-delete the customer
    $customer->delete();

    // Load order with the trashed customer so the trashed() guard is exercised
    $orderWithTrashedCustomer = $order->fresh();
    $orderWithTrashedCustomer->setRelation('customer', Customer::withTrashed()->find($customer->id));

    $result = $this->loyaltyService->awardPointsForOrder($orderWithTrashedCustomer);

    expect($result)->toBeNull();
});

it('awards points to active customer', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_tier' => 'bronze',
        'loyalty_points' => 0,
        'total_orders_count' => 0,
        'total_spent' => 0,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
        'total_amount' => 100.00,
        'tip_amount' => 0,
    ]);

    $transaction = $this->loyaltyService->awardPointsForOrder($order);

    expect($transaction)->not->toBeNull();
    expect($transaction->points)->toBeGreaterThan(0);
    expect($customer->fresh()->loyalty_points)->toBeGreaterThan(0);
});

it('adds bonus points within a transaction', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_tier' => 'bronze',
        'loyalty_points' => 100,
    ]);

    $transaction = $this->loyaltyService->addBonusPoints($customer, 50, 'Birthday bonus');

    expect($transaction->points)->toBe(50);
    expect($transaction->balance_after)->toBe(150);
    expect($customer->fresh()->loyalty_points)->toBe(150);
});
