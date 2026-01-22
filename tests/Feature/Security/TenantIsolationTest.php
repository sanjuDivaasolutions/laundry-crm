<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create two tenants for isolation testing
    $this->tenantA = Tenant::factory()->create(['name' => 'Tenant A', 'domain' => 'tenant-a', 'active' => true]);
    $this->tenantB = Tenant::factory()->create(['name' => 'Tenant B', 'domain' => 'tenant-b', 'active' => true]);

    // Create users for each tenant
    $this->userA = User::factory()->create(['tenant_id' => $this->tenantA->id, 'email' => 'user@tenant-a.com']);
    $this->userB = User::factory()->create(['tenant_id' => $this->tenantB->id, 'email' => 'user@tenant-b.com']);
});

describe('SEC-001: X-Tenant-ID Header Vulnerability Fix', function () {

    it('prevents authenticated user from accessing other tenant via X-Tenant-ID header', function () {
        // User A tries to access Tenant B's data via header manipulation
        $response = $this->actingAs($this->userA)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenantB->id,
                'Accept' => 'application/json',
            ])
            ->getJson('/api/v1/verify');

        // The tenant context should be User A's tenant, not Tenant B
        $tenantService = app(TenantService::class);

        // User should still be authenticated to their own tenant
        expect($tenantService->getId())->toBe($this->userA->tenant_id);
    });

    it('authenticated user tenant always takes precedence over header', function () {
        // Set up middleware resolution
        $tenantService = app(TenantService::class);

        // Simulate the request with header pointing to different tenant
        $request = Request::create('/api/v1/users', 'GET');
        $request->headers->set('X-Tenant-ID', (string) $this->tenantB->id);

        // After middleware, authenticated user's tenant should be set
        $this->actingAs($this->userA);

        $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
        $middleware->handle($request, fn($r) => response('ok'));

        // The tenant should be User A's tenant
        expect($tenantService->getId())->toBe($this->userA->tenant_id);
    });

    it('blocks unauthenticated header-based tenant access without valid signature', function () {
        $tenantService = app(TenantService::class);

        $request = Request::create('/api/v1/test', 'GET');
        $request->headers->set('X-Tenant-ID', (string) $this->tenantA->id);
        // No signature header - should not resolve tenant

        $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
        $middleware->handle($request, fn($r) => response('ok'));

        // Without valid signature, tenant should not be set from header
        // (will fall through to domain resolution which won't match)
        expect($tenantService->getId())->toBeNull();
    });

    it('allows domain-based tenant resolution for unauthenticated requests', function () {
        $tenantService = app(TenantService::class);

        $request = Request::create('/api/v1/test', 'GET', [], [], [], [
            'HTTP_HOST' => 'tenant-a',
        ]);

        $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
        $middleware->handle($request, fn($r) => response('ok'));

        expect($tenantService->getId())->toBe($this->tenantA->id);
    });

    it('rejects access to inactive tenant', function () {
        $inactiveTenant = Tenant::factory()->create(['active' => false, 'domain' => 'inactive']);
        $tenantService = app(TenantService::class);

        $request = Request::create('/api/v1/test', 'GET', [], [], [], [
            'HTTP_HOST' => 'inactive',
        ]);

        $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
        $response = $middleware->handle($request, fn($r) => response('ok'));

        expect($response->getStatusCode())->toBe(403);
        expect($response->getData()->error)->toBe('tenant_inactive');
    });

});

describe('SEC-005: BelongsToTenant Scope Protection', function () {

    it('automatically filters queries by current tenant', function () {
        // Set tenant context to Tenant A
        app(TenantService::class)->setTenant($this->tenantA);

        // Query should only return Tenant A's users
        $users = User::all();

        expect($users)->toHaveCount(1);
        expect($users->first()->tenant_id)->toBe($this->tenantA->id);
    });

    it('prevents access to other tenant data via direct ID', function () {
        // Set tenant context to Tenant A
        app(TenantService::class)->setTenant($this->tenantA);

        // Try to find User B (belongs to Tenant B)
        $user = User::find($this->userB->id);

        // Should not find the user due to tenant scope
        expect($user)->toBeNull();
    });

    it('auto-assigns tenant_id on model creation', function () {
        app(TenantService::class)->setTenant($this->tenantA);

        $newUser = User::create([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
        ]);

        expect($newUser->tenant_id)->toBe($this->tenantA->id);
    });

    it('returns empty results when no tenant context in strict mode', function () {
        // Ensure strict mode is enabled
        config(['tenancy.strict_scope' => true]);
        config(['tenancy.missing_context_action' => 'empty']);

        // Clear tenant context
        app(TenantService::class)->setTenant(null);

        // Query without tenant context should return empty
        $users = User::all();

        expect($users)->toBeEmpty();
    });

    it('logs tenant scope bypass when used', function () {
        // Use log facade spy to verify logging occurs
        $logSpy = Mockery::spy(\Psr\Log\LoggerInterface::class);
        app()->instance('log', $logSpy);

        // Use bypass method
        User::withoutTenantScope();

        // Verify info was called with message containing bypass
        $logSpy->shouldHaveReceived('info')
            ->once()
            ->withArgs(fn($message) => str_contains($message, 'Tenant scope bypass'));
    });

    it('allows explicit cross-tenant queries with forTenant scope', function () {
        app(TenantService::class)->setTenant($this->tenantA);

        // Query Tenant B's users explicitly
        $users = User::forTenant($this->tenantB->id)->get();

        expect($users)->toHaveCount(1);
        expect($users->first()->tenant_id)->toBe($this->tenantB->id);
    });

});

describe('Tenant Impersonation Security', function () {

    it('blocks impersonation without proper permission', function () {
        // User without impersonation permission
        $regularUser = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
        ]);

        $tenantService = app(TenantService::class);

        $request = Request::create('/api/v1/test', 'GET');
        $request->headers->set('X-Impersonate-Tenant', (string) $this->tenantB->id);

        // Mock authenticated user
        $this->actingAs($regularUser);

        $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
        $middleware->handle($request, fn($r) => response('ok'));

        // Should NOT allow impersonation
        expect($tenantService->getId())->toBe($regularUser->tenant_id);
    });

    it('logs impersonation attempts', function () {
        // Use log facade spy to verify logging occurs
        $logSpy = Mockery::spy(\Psr\Log\LoggerInterface::class);
        app()->instance('log', $logSpy);

        $regularUser = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
        ]);

        $tenantService = app(TenantService::class);

        $request = Request::create('/api/v1/test', 'GET');
        $request->headers->set('X-Impersonate-Tenant', (string) $this->tenantB->id);

        $this->actingAs($regularUser);

        $middleware = new \App\Http\Middleware\IdentifyTenant($tenantService);
        $middleware->handle($request, fn($r) => response('ok'));

        // Verify warning was called with message containing impersonation
        $logSpy->shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn($message) => str_contains($message, 'impersonation'));
    });

});
