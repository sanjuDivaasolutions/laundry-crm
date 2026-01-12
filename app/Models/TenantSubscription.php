<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class TenantSubscription extends CashierSubscription
{
    protected $table = 'tenant_subscriptions';

    /**
     * Get the subscription items related to the subscription.
     */
    public function items()
    {
        return $this->hasMany(\Laravel\Cashier\SubscriptionItem::class, 'subscription_id')->from('tenant_subscription_items');
    }
}
