<?php

use App\Services\TenantService;

it('returns default tenant id in single tenant mode when no tenant set', function () {
    config(['tenancy.single_tenant_mode' => true]);
    config(['tenancy.default_tenant_id' => 1]);

    $service = new TenantService;

    expect($service->getId())->toBe(1);
});

it('returns null when no tenant set in multi-tenant mode', function () {
    config(['tenancy.single_tenant_mode' => false]);

    $service = new TenantService;

    expect($service->getId())->toBeNull();
});
