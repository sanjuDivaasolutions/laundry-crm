<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * TenantSetting Model
 *
 * Key-value settings storage for tenant-specific configurations.
 * Supports type-safe retrieval with automatic casting based on the 'type' column.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $key
 * @property string|null $value
 * @property string $type (string, int, float, bool, json)
 * @property string|null $group
 * @property string|null $description
 */
class TenantSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Valid setting types.
     */
    public const TYPES = ['string', 'int', 'float', 'bool', 'json'];

    /**
     * Cache TTL in seconds (5 minutes).
     */
    protected const CACHE_TTL = 300;

    /**
     * Get the tenant that owns the setting.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the typed value of this setting.
     *
     * @return mixed
     */
    public function getTypedValueAttribute(): mixed
    {
        return self::castValue($this->value, $this->type);
    }

    /**
     * Cast a value to the specified type.
     */
    public static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Prepare a value for storage based on type.
     */
    public static function prepareValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'bool' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get a setting value for a tenant.
     *
     * @param int $tenantId
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(int $tenantId, string $key, mixed $default = null): mixed
    {
        $cacheKey = "tenant_setting:{$tenantId}:{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId, $key, $default) {
            $setting = static::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('key', $key)
                ->first();

            if (!$setting) {
                return $default;
            }

            return $setting->typed_value;
        });
    }

    /**
     * Set a setting value for a tenant.
     *
     * @param int $tenantId
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $group
     * @param string|null $description
     * @return static
     */
    public static function setValue(
        int $tenantId,
        string $key,
        mixed $value,
        string $type = 'string',
        ?string $group = null,
        ?string $description = null
    ): static {
        $preparedValue = self::prepareValue($value, $type);

        $setting = static::withoutGlobalScope('tenant')->updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            [
                'value' => $preparedValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        // Clear cache
        Cache::forget("tenant_setting:{$tenantId}:{$key}");
        Cache::forget("tenant_settings:{$tenantId}");

        return $setting;
    }

    /**
     * Get all settings for a tenant as a keyed array.
     *
     * @param int $tenantId
     * @param string|null $group Filter by group
     * @return array<string, mixed>
     */
    public static function getAllForTenant(int $tenantId, ?string $group = null): array
    {
        $cacheKey = $group
            ? "tenant_settings:{$tenantId}:group:{$group}"
            : "tenant_settings:{$tenantId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId, $group) {
            $query = static::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId);

            if ($group) {
                $query->where('group', $group);
            }

            return $query->get()
                ->mapWithKeys(fn ($setting) => [$setting->key => $setting->typed_value])
                ->toArray();
        });
    }

    /**
     * Delete a setting for a tenant.
     *
     * @param int $tenantId
     * @param string $key
     * @return bool
     */
    public static function deleteSetting(int $tenantId, string $key): bool
    {
        $deleted = static::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->delete();

        // Clear cache
        Cache::forget("tenant_setting:{$tenantId}:{$key}");
        Cache::forget("tenant_settings:{$tenantId}");

        return $deleted > 0;
    }

    /**
     * Clear all cached settings for a tenant.
     *
     * @param int $tenantId
     */
    public static function clearCache(int $tenantId): void
    {
        // Get all setting keys for this tenant
        $keys = static::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->pluck('key');

        foreach ($keys as $key) {
            Cache::forget("tenant_setting:{$tenantId}:{$key}");
        }

        Cache::forget("tenant_settings:{$tenantId}");
    }

    /**
     * Seed default settings for a new tenant.
     *
     * @param int $tenantId
     */
    public static function seedDefaults(int $tenantId): void
    {
        $defaults = [
            // General settings
            ['key' => 'company_name', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_phone', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_email', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_address', 'value' => '', 'type' => 'string', 'group' => 'general'],

            // Order settings
            ['key' => 'order_prefix', 'value' => 'ORD', 'type' => 'string', 'group' => 'orders'],
            ['key' => 'default_tax_rate', 'value' => '0', 'type' => 'float', 'group' => 'orders'],
            ['key' => 'allow_partial_payment', 'value' => '1', 'type' => 'bool', 'group' => 'orders'],

            // Notification settings
            ['key' => 'notify_order_created', 'value' => '1', 'type' => 'bool', 'group' => 'notifications'],
            ['key' => 'notify_order_ready', 'value' => '1', 'type' => 'bool', 'group' => 'notifications'],
            ['key' => 'notify_order_completed', 'value' => '1', 'type' => 'bool', 'group' => 'notifications'],
        ];

        foreach ($defaults as $setting) {
            static::setValue(
                $tenantId,
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['group']
            );
        }
    }
}
