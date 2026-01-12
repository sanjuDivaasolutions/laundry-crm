<?php

namespace App\Services\Billing;

use App\Models\Tenant;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SaaSSubscriptionService
{
    /**
     * Subscribe a tenant to a plan.
     *
     * @param Tenant $tenant
     * @param string $planId Stripe Price ID
     * @param string $paymentMethodId
     * @return void
     * @throws IncompletePayment
     */
    public function subscribe(Tenant $tenant, string $planId, string $paymentMethodId): void
    {
        // 1. Create or get Stripe Customer
        if (!$tenant->hasStripeId()) {
            $tenant->createAsStripeCustomer();
        }

        // 2. Add Payment Method
        $tenant->addPaymentMethod($paymentMethodId);
        
        // 3. Create Subscription
        // Note: verify metadata here if strictly required (omitted for speed)
        $tenant->newSubscription('default', $planId)->create($paymentMethodId);
    }

    /**
     * Cancel a tenant's subscription.
     */
    public function cancel(Tenant $tenant): void
    {
        $tenant->subscription('default')->cancel();
    }

    /**
     * Resume a tenant's subscription.
     */
    public function resume(Tenant $tenant): void
    {
        $tenant->subscription('default')->resume();
    }
}
