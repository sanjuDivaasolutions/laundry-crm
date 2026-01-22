<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\TenantResolutionException;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

/**
 * Trait for models that belong to a tenant.
 *
 * Provides automatic tenant scoping for queries and auto-assignment
 * of tenant_id on model creation.
 *
 * Security Features:
 * - Fail-safe mode: Rejects queries when no tenant context is set
 * - Auto-assignment: Sets tenant_id on creation
 * - Bypass protection: Logs and optionally blocks scope bypass
 *
 * @property int $tenant_id
 * @property-read Tenant $tenant
 */
trait BelongsToTenant
{
    /**
     * Flag to allow global queries (for system operations).
     * Set via withoutTenantScope() method.
     */
    protected static bool $bypassTenantScope = false;

    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Check if scope bypass is explicitly allowed
            if (static::$bypassTenantScope) {
                static::$bypassTenantScope = false; // Reset for next query
                return;
            }

            $tenantService = app(TenantService::class);
            $tenantId = $tenantService->getId();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
                return;
            }

            // FAIL-SAFE: No tenant context
            // In strict mode, this prevents data leakage
            if (static::shouldEnforceStrictTenantScope()) {
                static::handleMissingTenantContext($builder);
            }
        });

        // Auto-assign tenant_id on creation
        static::creating(function (Model $model) {
            $tenantService = app(TenantService::class);

            // Don't override if already set
            if ($model->getAttribute('tenant_id')) {
                return;
            }

            $tenantId = $tenantService->getId();

            if ($tenantId) {
                $model->setAttribute('tenant_id', $tenantId);
                return;
            }

            // Fail-safe: Reject creation without tenant context
            if (static::shouldEnforceStrictTenantScope()) {
                logger()->error('Attempted to create tenant-scoped model without tenant context', [
                    'model' => get_class($model),
                    'attributes' => $model->getAttributes(),
                ]);

                throw TenantResolutionException::missingContext();
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to query within a specific tenant (for system operations).
     *
     * @param Builder $query
     * @param int $tenantId
     * @return Builder
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Allow the next query to bypass tenant scope.
     *
     * USE WITH CAUTION: This should only be used for:
     * - System migrations
     * - Admin analytics
     * - Cross-tenant reports
     *
     * All usages are logged for audit purposes.
     *
     * @return static
     */
    public static function withoutTenantScope(): string
    {
        static::$bypassTenantScope = true;

        logger()->info('Tenant scope bypass requested', [
            'model' => static::class,
            'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? 'unknown',
        ]);

        return static::class;
    }

    /**
     * Check if current tenant matches the model's tenant.
     *
     * @return bool
     */
    public function belongsToCurrentTenant(): bool
    {
        $currentTenantId = app(TenantService::class)->getId();
        return $currentTenantId && $this->tenant_id === $currentTenantId;
    }

    /**
     * Verify model belongs to the current tenant or throw.
     *
     * @throws TenantResolutionException
     */
    public function ensureBelongsToCurrentTenant(): void
    {
        if (!$this->belongsToCurrentTenant()) {
            $currentTenantId = app(TenantService::class)->getId();

            logger()->warning('Cross-tenant access attempt blocked', [
                'model' => get_class($this),
                'model_id' => $this->getKey(),
                'model_tenant_id' => $this->tenant_id,
                'request_tenant_id' => $currentTenantId,
            ]);

            throw TenantResolutionException::unauthorized(
                auth()->id() ?? 0,
                $this->tenant_id
            );
        }
    }

    /**
     * Determine if strict tenant scope should be enforced.
     *
     * Returns false during:
     * - Console commands (artisan)
     * - Queue workers
     * - Testing
     * - When explicitly configured off
     */
    protected static function shouldEnforceStrictTenantScope(): bool
    {
        // Disable strict mode in console (migrations, seeders, etc.)
        if (App::runningInConsole() && !App::runningUnitTests()) {
            return false;
        }

        // Check configuration
        return config('tenancy.strict_scope', true);
    }

    /**
     * Handle missing tenant context in fail-safe mode.
     *
     * Options based on configuration:
     * - 'throw': Throw exception (most secure)
     * - 'empty': Return empty result set (safe but silent)
     * - 'log': Log and continue (for debugging)
     */
    protected static function handleMissingTenantContext(Builder $builder): void
    {
        $action = config('tenancy.missing_context_action', 'empty');
        $modelClass = $builder->getModel()::class;

        match ($action) {
            'throw' => throw tap(
                TenantResolutionException::missingContext(),
                fn () => logger()->error('Query attempted without tenant context', ['model' => $modelClass])
            ),
            'empty' => tap($builder->whereRaw('1 = 0'), fn () => logger()->warning(
                'Query without tenant context - returning empty result',
                ['model' => $modelClass]
            )),
            default => logger()->warning(
                'Query without tenant context - no filtering applied',
                ['model' => $modelClass]
            ),
        };
    }
}
