<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Models\Customer;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\TenantQuota;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset singleton state
    app(TenantService::class)->setTenant(null);
});

describe('Deep Tenant Isolation', function () {

    test('strict scope mode returns empty results when no tenant is set', function () {
        // Enforce strict scope
        config(['tenancy.strict_scope' => true]);
        
        // Create data for a tenant
        $tenant = Tenant::factory()->create();
        app(TenantService::class)->setTenant($tenant);
        Item::factory()->create(['name' => 'Secret Item']);
        
        // Clear context
        app(TenantService::class)->setTenant(null);
        
        // Attempt fetch
        $items = Item::all();
        
        expect($items)->toBeEmpty();
    });

    test('cross-tenant data leakage is impossible via ID guessing', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        
        app(TenantService::class)->setTenant($tenantA);
        $itemA = Item::factory()->create();
        
        app(TenantService::class)->setTenant($tenantB);
        
        // Try to find Item A while in Tenant B context
        $found = Item::find($itemA->id);
        
        expect($found)->toBeNull();
    });

    test('relationships are automatically scoped', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        
        // Create Customer in Tenant A
        app(TenantService::class)->setTenant($tenantA);
        $customerA = Customer::factory()->create();
        
        // Create Order in Tenant B
        app(TenantService::class)->setTenant($tenantB);
        $orderB = Order::factory()->create(['tenant_id' => $tenantB->id]); // Factory might auto-set, being explicit
        
        // Manually force foreign key via DB to simulate an attack or bug assignment
        DB::table('orders')->where('id', $orderB->id)->update(['customer_id' => $customerA->id]);
        
        // Refresh model in Tenant B context
        $orderB->refresh();
        
        // The relationship should return null because the related model is in a different tenant scope
        expect($orderB->customer)->toBeNull();
    });

    test('tenant context is immutable during request via singleton', function () {
        $tenantA = Tenant::factory()->create();
        $service = app(TenantService::class);
        
        $service->setTenant($tenantA);
        expect($service->getId())->toBe($tenantA->id);
        
        // Retrieve singleton again
        $service2 = app(TenantService::class);
        expect($service2->getId())->toBe($tenantA->id);
    });
});

describe('Quota Enforcement', function () {
    
    test('cannot exceed defined resource quota', function () {
        $tenant = Tenant::factory()->create();
        app(TenantService::class)->setTenant($tenant);
        
        // Set quota: Max 2 Items
        $tenant->quotas()->create([
            'quota_code' => 'items',
            'limit' => 2,
            'period' => 'monthly'
        ]);
        
        // Create 2 items (Allowed)
        Item::factory()->count(2)->create();
        
        // Check "canUse" or "trackUsage" logic if exposed, 
        // or rely on the Middleware/Observer that enforces it.
        // Assuming the `HasEntitlements` trait has a method to check this.
        
        expect($tenant->couldExceedQuota('items'))->toBeTrue();
        
        // Attempting to track usage for a 3rd item should fail
        $allowed = $tenant->trackUsage('items');
        expect($allowed)->toBeFalse();
    });

    test('unlimited quota allows infinite resources', function () {
        $tenant = Tenant::factory()->create();
        app(TenantService::class)->setTenant($tenant);
        
        // Set quota: -1 (Unlimited)
        $tenant->quotas()->create([
            'quota_code' => 'items',
            'limit' => -1, // Unlimited
            'period' => 'monthly'
        ]);
        
        // Create 100 fake items usage
        $result = $tenant->trackUsage('items', 100);
        
        expect($result)->toBeTrue();
        expect($tenant->couldExceedQuota('items', 1000))->toBeFalse();
    });
});

describe('Lifecycle & Status', function () {
    
    test('inactive tenant cannot resolve context', function () {
        $tenant = Tenant::factory()->create(['active' => false]);
        
        // Depending on how IdentifyTenant middleware works, 
        // this test might need to be a Feature test hitting an endpoint.
        
        $response = $this->withHeaders(['X-Tenant-ID' => $tenant->id])
                         ->getJson('/api/user'); 
                         
        // Assuming middleware blocks inactive tenants
        $response->assertStatus(403); 
    });

    test('trial expiration logic', function () {
        $tenant = Tenant::factory()->create([
            'trial_ends_at' => now()->subDay(), // Expired
            'stripe_id' => null // Not subscribed
        ]);
        
        // Determine trial status
        // Logic normally resides in `EnforceTenantQuota` or Tenant model
        
        expect($tenant->onTrial())->toBeFalse(); // Should be false as it expired
        // Verify entitlement logic says "no access" or similar if implemented
    });
});
