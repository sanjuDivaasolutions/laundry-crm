<?php

namespace App\Traits;

use App\Services\TenantService;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantService = app(TenantService::class);
            
            // Only apply scope if a tenant is identified
            if ($tenantId = $tenantService->getId()) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model) {
            $tenantService = app(TenantService::class);
            
            if (! $model->getAttribute('tenant_id') && $tenantId = $tenantService->getId()) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
