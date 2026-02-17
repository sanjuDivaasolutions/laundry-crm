<?php

use App\Enums\PaymentMethodEnum;
use App\Models\Payment;
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

test('payment method enum has apple pay', function () {
    $applePay = PaymentMethodEnum::ApplePay;

    expect($applePay->value)->toBe('apple_pay');
    expect($applePay->getLabel())->toBe('Apple Pay');
    expect($applePay->getColor())->toBe('dark');
    expect($applePay->getIcon())->toBe('heroicon-m-device-phone-mobile');
});

test('payment method enum has google pay', function () {
    $googlePay = PaymentMethodEnum::GooglePay;

    expect($googlePay->value)->toBe('google_pay');
    expect($googlePay->getLabel())->toBe('Google Pay');
    expect($googlePay->getColor())->toBe('primary');
});

test('payment method enum does not have upi', function () {
    $cases = array_map(fn ($case) => $case->value, PaymentMethodEnum::cases());

    expect($cases)->not->toContain('upi');
    expect($cases)->toContain('cash');
    expect($cases)->toContain('card');
    expect($cases)->toContain('apple_pay');
    expect($cases)->toContain('google_pay');
    expect($cases)->toContain('other');
});

test('payment can be created with apple pay method', function () {
    $payment = Payment::factory()->create([
        'tenant_id' => $this->tenant->id,
        'payment_method' => PaymentMethodEnum::ApplePay,
        'created_at' => now(),
    ]);

    expect($payment->payment_method)->toBe(PaymentMethodEnum::ApplePay);
    expect($payment->payment_method->getLabel())->toBe('Apple Pay');
});

test('payment can be created with google pay method', function () {
    $payment = Payment::factory()->create([
        'tenant_id' => $this->tenant->id,
        'payment_method' => PaymentMethodEnum::GooglePay,
        'created_at' => now(),
    ]);

    expect($payment->payment_method)->toBe(PaymentMethodEnum::GooglePay);
    expect($payment->payment_method->getLabel())->toBe('Google Pay');
});

test('all payment method labels are correct', function () {
    $expected = [
        'cash' => 'Cash',
        'card' => 'Card',
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
        'other' => 'Other',
    ];

    foreach (PaymentMethodEnum::cases() as $case) {
        expect($case->getLabel())->toBe($expected[$case->value]);
    }
});
