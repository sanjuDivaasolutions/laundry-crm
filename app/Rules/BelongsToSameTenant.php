<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\TenantService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * BelongsToSameTenant Validation Rule
 *
 * Validates that a foreign key reference belongs to the same tenant as the current context.
 * This prevents cross-tenant data access through FK manipulation.
 *
 * Security: This rule is critical for multi-tenant data isolation.
 * Without it, a malicious user could submit a valid ID from another tenant
 * in a request, potentially accessing or linking to unauthorized data.
 *
 * @example
 * // In a FormRequest
 * 'category_id' => ['nullable', 'integer', new BelongsToSameTenant(Category::class)],
 * 'buyer_id' => ['required', 'integer', new BelongsToSameTenant('buyers')],
 */
class BelongsToSameTenant implements ValidationRule
{
    /**
     * The model class or table name to check against.
     */
    protected string $modelOrTable;

    /**
     * The column name for tenant ID in the target table.
     */
    protected string $tenantColumn;

    /**
     * The primary key column name.
     */
    protected string $primaryKey;

    /**
     * Optional custom error message.
     */
    protected ?string $customMessage;

    /**
     * Create a new rule instance.
     *
     * @param string $modelOrTable Model class name or table name
     * @param string $tenantColumn Column name for tenant_id (default: 'tenant_id')
     * @param string $primaryKey Primary key column name (default: 'id')
     * @param string|null $customMessage Optional custom error message
     */
    public function __construct(
        string $modelOrTable,
        string $tenantColumn = 'tenant_id',
        string $primaryKey = 'id',
        ?string $customMessage = null
    ) {
        $this->modelOrTable = $modelOrTable;
        $this->tenantColumn = $tenantColumn;
        $this->primaryKey = $primaryKey;
        $this->customMessage = $customMessage;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute The attribute being validated
     * @param mixed $value The value to validate
     * @param Closure $fail Closure to call on failure
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow null values - use 'required' rule if needed
        if ($value === null || $value === '') {
            return;
        }

        // Ensure value is a valid ID
        if (!is_numeric($value) || $value <= 0) {
            $fail('The :attribute must be a valid ID.');
            return;
        }

        // Get current tenant ID
        $tenantService = app(TenantService::class);
        $currentTenantId = $tenantService->getId();

        // If no tenant context, validation depends on strictness configuration
        if (!$currentTenantId) {
            // In strict mode, fail the validation
            if (config('tenancy.strict_scope', true)) {
                $fail('Unable to validate :attribute: no tenant context.');
                logger()->error('BelongsToSameTenant validation without tenant context', [
                    'attribute' => $attribute,
                    'value' => $value,
                    'model' => $this->modelOrTable,
                ]);
                return;
            }
            // In non-strict mode, skip validation (console/testing)
            return;
        }

        // Determine table name
        $table = $this->resolveTableName();

        // Check if record exists AND belongs to current tenant
        $exists = DB::table($table)
            ->where($this->primaryKey, $value)
            ->where($this->tenantColumn, $currentTenantId)
            ->exists();

        if (!$exists) {
            // Check if record exists at all (for better error message)
            $existsInOtherTenant = DB::table($table)
                ->where($this->primaryKey, $value)
                ->exists();

            if ($existsInOtherTenant) {
                // Security: Log cross-tenant access attempt
                logger()->warning('Cross-tenant FK reference attempt blocked', [
                    'attribute' => $attribute,
                    'value' => $value,
                    'table' => $table,
                    'current_tenant_id' => $currentTenantId,
                    'user_id' => auth()->id(),
                    'request_url' => request()?->fullUrl(),
                ]);

                $fail($this->customMessage ?? 'The selected :attribute is not accessible.');
            } else {
                $fail($this->customMessage ?? 'The selected :attribute does not exist.');
            }
        }
    }

    /**
     * Resolve the table name from model class or string.
     */
    protected function resolveTableName(): string
    {
        // If it's a class name, instantiate and get table
        if (class_exists($this->modelOrTable)) {
            /** @var Model $model */
            $model = new $this->modelOrTable();
            return $model->getTable();
        }

        // Otherwise, treat as table name directly
        return $this->modelOrTable;
    }

    /**
     * Static factory for common use cases.
     *
     * @param string $modelOrTable
     * @return static
     */
    public static function for(string $modelOrTable): static
    {
        return new static($modelOrTable);
    }

    /**
     * Set a custom error message.
     */
    public function message(string $message): static
    {
        $this->customMessage = $message;
        return $this;
    }
}
