<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * TenantValidationServiceProvider
 *
 * Provides custom validation rules for multi-tenant FK validation.
 *
 * Available Rules:
 * - exists_tenant:table,column - Validates ID exists and belongs to current tenant
 * - unique_tenant:table,column - Validates uniqueness within current tenant
 */
class TenantValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerExistsTenantRule();
        $this->registerUniqueTenantRule();
    }

    /**
     * Register the exists_tenant validation rule.
     *
     * Usage: 'category_id' => ['nullable', 'exists_tenant:categories,id']
     *
     * This validates that:
     * 1. The record exists in the specified table
     * 2. The record belongs to the current tenant (has same tenant_id)
     */
    protected function registerExistsTenantRule(): void
    {
        Validator::extend('exists_tenant', function ($attribute, $value, $parameters, $validator) {
            if (empty($value)) {
                return true; // Use 'required' rule if value must be present
            }

            if (count($parameters) < 1) {
                return false;
            }

            $table = $parameters[0];
            $column = $parameters[1] ?? 'id';
            $tenantColumn = $parameters[2] ?? 'tenant_id';

            $tenantService = app(TenantService::class);
            $tenantId = $tenantService->getId();

            // If no tenant context and strict mode is enabled, fail
            if (!$tenantId && config('tenancy.strict_scope', true)) {
                logger()->error('exists_tenant validation without tenant context', [
                    'attribute' => $attribute,
                    'table' => $table,
                ]);
                return false;
            }

            // If no tenant context and non-strict mode, skip validation
            if (!$tenantId) {
                return true;
            }

            $exists = DB::table($table)
                ->where($column, $value)
                ->where($tenantColumn, $tenantId)
                ->exists();

            if (!$exists) {
                // Log potential cross-tenant access attempt
                $existsGlobally = DB::table($table)->where($column, $value)->exists();
                if ($existsGlobally) {
                    logger()->warning('Cross-tenant FK reference attempt blocked', [
                        'attribute' => $attribute,
                        'value' => $value,
                        'table' => $table,
                        'tenant_id' => $tenantId,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            return $exists;
        });

        Validator::replacer('exists_tenant', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The selected :attribute is not accessible or does not exist.');
        });
    }

    /**
     * Register the unique_tenant validation rule.
     *
     * Usage: 'code' => ['required', 'unique_tenant:items,code']
     *
     * This validates that the value is unique within the current tenant.
     * Useful for codes, names, etc. that should be unique per tenant but
     * can exist across different tenants.
     */
    protected function registerUniqueTenantRule(): void
    {
        Validator::extend('unique_tenant', function ($attribute, $value, $parameters, $validator) {
            if (empty($value)) {
                return true;
            }

            if (count($parameters) < 1) {
                return false;
            }

            $table = $parameters[0];
            $column = $parameters[1] ?? $attribute;
            $exceptId = $parameters[2] ?? null;
            $idColumn = $parameters[3] ?? 'id';
            $tenantColumn = $parameters[4] ?? 'tenant_id';

            $tenantService = app(TenantService::class);
            $tenantId = $tenantService->getId();

            // If no tenant context, skip validation
            if (!$tenantId) {
                return true;
            }

            $query = DB::table($table)
                ->where($column, $value)
                ->where($tenantColumn, $tenantId);

            // Exclude current record if updating
            if ($exceptId) {
                $query->where($idColumn, '!=', $exceptId);
            }

            return !$query->exists();
        });

        Validator::replacer('unique_tenant', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute has already been taken.');
        });
    }
}
