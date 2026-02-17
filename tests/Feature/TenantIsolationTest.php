<?php

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('models are scoped to current tenant', function () {
    // Create two tenants
    $tenant1 = Tenant::create(['name' => 'Tenant 1', 'domain' => 'tenant1.test', 'active' => true]);
    $tenant2 = Tenant::create(['name' => 'Tenant 2', 'domain' => 'tenant2.test', 'active' => true]);

    // Set Tenant 1 context
    $tenantService = app(TenantService::class);
    $tenantService->setTenant($tenant1);

    // Create User in Tenant 1
    $user1 = User::factory()->create(['email' => 'user@tenant1.com', 'name' => 'User T1']);

    // Create Company in Tenant 1
    $company1 = Company::create([
        'name' => 'Company T1',
        'code' => 'C1',
        'address_1' => 'Addr 1',
        'address_2' => '',
        'active' => true,
        'user_id' => $user1->id,
    ]);

    // Verify tenant_id was automatically set
    expect($user1->tenant_id)->toBe($tenant1->id);
    expect($company1->tenant_id)->toBe($tenant1->id);

    // Switch to Tenant 2
    $tenantService->setTenant($tenant2);

    // Create User in Tenant 2
    $user2 = User::factory()->create(['email' => 'user@tenant2.com', 'name' => 'User T2']);

    // Verify tenant_id was automatically set
    expect($user2->tenant_id)->toBe($tenant2->id);

    // Verify User 1 is NOT visible in Tenant 2 scope
    expect(User::find($user1->id))->toBeNull();

    // Verify Company 1 is NOT visible in Tenant 2 scope
    expect(Company::find($company1->id))->toBeNull();

    // Verify User 2 IS visible in Tenant 2 scope
    expect(User::find($user2->id)->id)->toBe($user2->id);

    // Switch back to Tenant 1
    $tenantService->setTenant($tenant1);

    // Verify User 1 IS visible again
    expect(User::find($user1->id)->id)->toBe($user1->id);

    // Verify User 2 is NOT visible in Tenant 1 scope
    expect(User::find($user2->id))->toBeNull();
});

test('middleware identifies tenant from domain', function () {
    $tenant = Tenant::create(['name' => 'Header Tenant', 'domain' => 'header.test', 'active' => true]);
    $tenantService = app(TenantService::class);

    // Test domain-based resolution directly via the middleware
    $request = Request::create('/api/v1/test', 'GET', [], [], [], [
        'HTTP_HOST' => 'header.test',
    ]);

    $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
    $middleware->handle($request, fn ($r) => response('ok'));

    expect($tenantService->getId())->toBe($tenant->id);
});
