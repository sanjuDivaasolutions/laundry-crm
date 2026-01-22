<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasEntitlements;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use Billable, HasEntitlements, HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'active',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class);
    }

    /**
     * Check if the tenant has already used their trial.
     */
    public function hasUsedTrial(): bool
    {
        // If they have trial_ends_at set, they've used trial
        if ($this->trial_ends_at !== null) {
            return true;
        }

        // Check if they've ever had a subscription
        return $this->subscriptions()->exists();
    }

    /**
     * Get the current plan code from active subscription.
     */
    public function getCurrentPlanCode(): ?string
    {
        $subscription = $this->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return null;
        }

        // Get from subscription metadata or items
        return $subscription->stripe_price;
    }

    /**
     * Get the table associated with the model.
     */
    public function subscriptions()
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'tenant_id')->from('tenant_subscriptions');
    }
}
