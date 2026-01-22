<?php

use App\Models\Tenant;
use App\Models\TenantSubscription;

try {
    echo "--- Starting Manual Billing Verification ---\n";

    // 1. Cleanup old test data
    Tenant::where('domain', 'billing-test.local')->delete();

    // 2. Create Tenant
    $tenant = Tenant::create([
        'name' => 'Test Billing Tenant',
        'domain' => 'billing-test.local',
        'active' => true,
    ]);
    echo "Created Tenant: {$tenant->id}\n";

    // 3. Verify Custom Subscription Model & Table
    $subscription = new TenantSubscription();
    $subscription->tenant_id = $tenant->id;
    $subscription->type = 'default';
    $subscription->stripe_id = 'sub_test_' . uniqid();
    $subscription->stripe_status = 'active';
    $subscription->save(); // Should save to 'tenant_subscriptions'

    echo "Created Subscription: {$subscription->id}\n";

    // 4. Verify Relationship
    $retrievedSub = $tenant->subscriptions()->first();
    
    if (!$retrievedSub) {
        throw new Exception("Relationship failed: Could not retrieve subscription via \$tenant->subscriptions()");
    }

    echo "Retrieved Subscription via Relationship: {$retrievedSub->stripe_id}\n";

    // 5. Verify Table Name explicitly
    $tableName = $retrievedSub->getTable();
    if ($tableName !== 'tenant_subscriptions') {
        throw new Exception("FAILED: Expected table 'tenant_subscriptions', got '{$tableName}'");
    }
    echo "SUCCESS: Subscription matches table '{$tableName}'\n";

    // 6. Verify Billable Trait is active
    if (!method_exists($tenant, 'newSubscription')) {
        throw new Exception("FAILED: Tenant model does not have Billable trait methods.");
    }
    echo "SUCCESS: Tenant model has Billable trait.\n";

    echo "--- Verification Completed Successfully ---\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
