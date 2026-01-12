<?php

declare(strict_types=1);

namespace App\Traits;

use App\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            // Auto-assign tenant_id if not set and user is logged in
            if (! $model->getAttribute('tenant_id') && Auth::check()) {
                $model->setAttribute('tenant_id', Auth::user()->tenant_id);
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     *
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
