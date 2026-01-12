<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use Billable;

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
     * Get the table associated with the model.
     */
    public function subscriptions()
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'tenant_id')->from('tenant_subscriptions');
    }
}
