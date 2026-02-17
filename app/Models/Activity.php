<?php

namespace App\Models;

use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    /**
     * Boot the model and add tenant scoping.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = app(TenantService::class)->getId();

            if ($tenantId) {
                $builder->where('activity_log.tenant_id', $tenantId);
            }
        });

        static::creating(function (self $activity) {
            if (! $activity->tenant_id) {
                $activity->tenant_id = app(TenantService::class)->getId();
            }
        });
    }

    /**
     * Get the tenant that owns the activity.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
