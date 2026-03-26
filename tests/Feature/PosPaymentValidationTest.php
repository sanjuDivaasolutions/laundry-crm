<?php

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PosService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

it('rejects payment exceeding order balance', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 80.00,
        'balance_amount' => 20.00,
        'payment_status' => PaymentStatusEnum::Partial,
    ]);

    $posService = app(PosService::class);

    $posService->processPayment($order, [
        'amount' => 50.00,
        'payment_method' => 'cash',
    ]);
})->throws(ValidationException::class, 'Payment amount');

it('accepts payment equal to order balance', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 80.00,
        'balance_amount' => 20.00,
        'payment_status' => PaymentStatusEnum::Partial,
    ]);

    $posService = app(PosService::class);

    $result = $posService->processPayment($order, [
        'amount' => 20.00,
        'payment_method' => 'cash',
    ]);

    expect($result['order']->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('accepts partial payment within balance', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 0,
        'balance_amount' => 100.00,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    $posService = app(PosService::class);

    $result = $posService->processPayment($order, [
        'amount' => 50.00,
        'payment_method' => 'cash',
    ]);

    expect($result['order']->payment_status)->toBe(PaymentStatusEnum::Partial);
    expect((float) $result['order']->balance_amount)->toBe(50.00);
});

it('rejects negative tip amount', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 0,
        'balance_amount' => 100.00,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    $posService = app(PosService::class);

    $posService->processPayment($order, [
        'amount' => 50.00,
        'payment_method' => 'cash',
        'tip_amount' => -10.00,
    ]);
})->throws(ValidationException::class, 'Tip amount');
