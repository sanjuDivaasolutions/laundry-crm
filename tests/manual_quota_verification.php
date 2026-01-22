<?php

use App\Models\Tenant;
use App\Models\TenantQuota;
use App\Models\TenantUsage;
use App\Services\TenantService;
use Illuminate\Http\Request;

try {
    echo "--- Starting Manual Entitlement Verification ---\n";

    // 1. Setup Tenant
    Tenant::where('domain', 'quota-test.local')->delete();
    $tenant = Tenant::create([
        'name' => 'Quota Test Tenant',
        'domain' => 'quota-test.local',
        'active' => true,
    ]);
    
    // Set as current tenant for middleware simulation
    app(TenantService::class)->setTenant($tenant);

    echo "Created Tenant: {$tenant->id}\n";

    // 2. Define Quota (Max 2 items)
    $tenant->quotas()->create([
        'quota_code' => 'max_items',
        'limit' => 2
    ]);
    echo "Defined Quota: max_items = 2\n";

    // 3. Test Logic (Trait)
    
    // Usage 0 -> Check 1 (Allowed)
    if (!$tenant->couldExceedQuota('max_items', 1)) {
        echo "[PASS] Logic: Usage 0 + 1 <= 2\n";
    } else {
        throw new Exception("[FAIL] Logic: Usage 0 + 1 flagged as exceeded");
    }

    // Increment Usage to 2
    $tenant->trackUsage('max_items', 2);
    echo "Usage incremented to 2\n";

    // Usage 2 -> Check 1 (Blocked)
    if ($tenant->couldExceedQuota('max_items', 1)) {
        echo "[PASS] Logic: Usage 2 + 1 > 2\n";
    } else {
        throw new Exception("[FAIL] Logic: Usage 2 + 1 NOT flagged as exceeded");
    }

    // 4. Test Middleware Logic
    // We instantiate the middleware directly to test logic without full HTTP stack
    $middleware = new \App\Http\Middleware\EnforceTenantQuota();
    $request = Request::create('/test', 'POST');
    
    // Case A: Quota Exceeded
    $response = $middleware->handle($request, function($req) {
        return new \Symfony\Component\HttpFoundation\Response('OK');
    }, 'max_items');

    if ($response->getStatusCode() === 403) {
        echo "[PASS] Middleware: Blocked request (403) when quota exceeded\n";
    } else {
        throw new Exception("[FAIL] Middleware: Did not block request. Status: " . $response->getStatusCode());
    }

    echo "--- Verification Completed Successfully ---\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
