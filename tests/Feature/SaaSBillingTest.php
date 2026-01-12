<?php

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\Billing\SaaSSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Payment;

uses(RefreshDatabase::class);

test('tenant can subscribe to a plan', function () {
    // 1. Setup Tenant
    $tenant = Tenant::create([
        'name' => 'Billing Tenant',
        'domain' => 'billing.test',
        'active' => true,
    ]);

    // 2. Mock Stripe Interactions on the Tenant model
    // Since Cashier makes direct API calls, we need to partial mock the Tenant
    // OR we can rely on Cashier's built-in testing helpers if available, 
    // but often mocking the methods that hit the API is safer for unit/feature tests without keys.
    
    // For this test, we accept that Cashier works (creating the subscription record) 
    // if we bypass the actual API call logic.
    // However, properly testing Cashier without API keys is tricky. 
    // We will verify the *Service* logic calls the right methods.

    $service = new SaaSSubscriptionService();
    
    // Mock the tenant methods that hit Stripe
    $tenant = Mockery::mock($tenant)->makePartial();
    $tenant->shouldReceive('hasStripeId')->andReturn(true);
    $tenant->shouldReceive('addPaymentMethod')->once();
    
    // Mock the Subscription Builder
    $builder = Mockery::mock(Laravel\Cashier\SubscriptionBuilder::class);
    $builder->shouldReceive('create')->once()->andReturn(new TenantSubscription());

    $tenant->shouldReceive('newSubscription')->with('default', 'price_123')->andReturn($builder);

    // 3. call service
    $service->subscribe($tenant, 'price_123', 'pm_card_visa');
    
    // Assertions handled by Mockery expectations
});

test('tenant subscription table is used', function () {
    $tenant = Tenant::create([
        'name' => 'Table Tenant',
        'domain' => 'table.test',
        'active' => true,
    ]);

    // Check relationship
    $relation = $tenant->subscriptions();
    expect($relation->getModel()->getTable())->toBe('tenant_subscriptions');
});
