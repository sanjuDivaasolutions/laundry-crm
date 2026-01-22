<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\QuotaPeriod;
use App\Models\TenantFeature;
use App\Models\TenantQuota;
use App\Models\TenantUsage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Trait for managing tenant entitlements (features and quotas).
 *
 * Provides feature flags, quota limits, and usage tracking
 * with atomic operations to prevent race conditions.
 */
trait HasEntitlements
{
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the tenant's enabled features.
     */
    public function features(): HasMany
    {
        return $this->hasMany(TenantFeature::class);
    }

    /**
     * Get the tenant's quota definitions.
     */
    public function quotas(): HasMany
    {
        return $this->hasMany(TenantQuota::class);
    }

    /**
     * Get the tenant's usage records.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(TenantUsage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Feature Checking
    |--------------------------------------------------------------------------
    */

    /**
     * Check if tenant has a feature enabled.
     *
     * @param string $featureCode The feature code to check
     * @return bool True if feature is enabled and not expired
     */
    public function canUse(string $featureCode): bool
    {
        $feature = $this->features()
            ->where('feature_code', $featureCode)
            ->first();

        if (!$feature) {
            return false;
        }

        if (!$feature->enabled) {
            return false;
        }

        // Check expiration
        if ($feature->expires_at && $feature->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Enable a feature for the tenant.
     *
     * @param string $featureCode The feature code to enable
     * @param Carbon|null $expiresAt Optional expiration date
     * @return TenantFeature The created or updated feature record
     */
    public function enableFeature(string $featureCode, ?Carbon $expiresAt = null): TenantFeature
    {
        return $this->features()->updateOrCreate(
            ['feature_code' => $featureCode],
            [
                'enabled' => true,
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Disable a feature for the tenant.
     *
     * @param string $featureCode The feature code to disable
     * @return bool True if feature was disabled
     */
    public function disableFeature(string $featureCode): bool
    {
        return $this->features()
            ->where('feature_code', $featureCode)
            ->update(['enabled' => false]) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Quota Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get the defined quota limit for a code.
     *
     * @param string $quotaCode The quota code to check
     * @return int The limit, or 0 if not defined. -1 means unlimited.
     */
    public function getQuota(string $quotaCode): int
    {
        $quota = $this->quotas()
            ->where('quota_code', $quotaCode)
            ->first();

        return $quota ? (int) $quota->limit : 0;
    }

    /**
     * Get the quota period for a code.
     *
     * @param string $quotaCode The quota code to check
     * @return QuotaPeriod|null The period, or null if not defined
     */
    public function getQuotaPeriod(string $quotaCode): ?QuotaPeriod
    {
        $quota = $this->quotas()
            ->where('quota_code', $quotaCode)
            ->first();

        if (!$quota || !$quota->period) {
            return null;
        }

        return QuotaPeriod::tryFrom($quota->period);
    }

    /**
     * Set a quota limit for the tenant.
     *
     * @param string $quotaCode The quota code
     * @param int $limit The limit (-1 for unlimited)
     * @param QuotaPeriod $period The reset period
     * @return TenantQuota The created or updated quota record
     */
    public function setQuota(string $quotaCode, int $limit, QuotaPeriod $period = QuotaPeriod::MONTHLY): TenantQuota
    {
        return $this->quotas()->updateOrCreate(
            ['quota_code' => $quotaCode],
            [
                'limit' => $limit,
                'period' => $period->value,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Usage Tracking (Atomic Operations)
    |--------------------------------------------------------------------------
    */

    /**
     * Get current usage for a quota.
     *
     * Automatically resets usage if the period has elapsed.
     *
     * @param string $quotaCode The quota code to check
     * @return int The current usage count
     */
    public function getUsage(string $quotaCode): int
    {
        $usage = $this->usages()
            ->where('quota_code', $quotaCode)
            ->first();

        if (!$usage) {
            return 0;
        }

        // Check if usage needs to be reset based on period
        if ($this->shouldResetUsage($quotaCode, $usage)) {
            $this->resetUsage($quotaCode);
            return 0;
        }

        return (int) $usage->current_usage;
    }

    /**
     * Get remaining quota for a code.
     *
     * @param string $quotaCode The quota code to check
     * @return int Remaining quota. Returns PHP_INT_MAX for unlimited.
     */
    public function getRemainingQuota(string $quotaCode): int
    {
        $limit = $this->getQuota($quotaCode);

        // Unlimited
        if ($limit === -1) {
            return PHP_INT_MAX;
        }

        $usage = $this->getUsage($quotaCode);

        return max(0, $limit - $usage);
    }

    /**
     * Check if an action would exceed the quota.
     *
     * This is a READ-ONLY check. Does not modify usage.
     *
     * @param string $quotaCode The quota code to check
     * @param int $amount The amount to check (default 1)
     * @return bool True if action WOULD exceed quota
     */
    public function couldExceedQuota(string $quotaCode, int $amount = 1): bool
    {
        $limit = $this->getQuota($quotaCode);

        // Unlimited quota
        if ($limit === -1) {
            return false;
        }

        // Zero limit means feature is disabled
        if ($limit === 0) {
            return true;
        }

        $currentUsage = $this->getUsage($quotaCode);

        return ($currentUsage + $amount) > $limit;
    }

    /**
     * Atomically increment usage for a quota.
     *
     * Uses database-level atomic operations to prevent race conditions.
     * This is the SAFE way to track usage under concurrent load.
     *
     * @param string $quotaCode The quota code to increment
     * @param int $amount The amount to increment (default 1)
     * @return bool True if usage was incremented, false if quota exceeded
     */
    public function trackUsage(string $quotaCode, int $amount = 1): bool
    {
        $limit = $this->getQuota($quotaCode);

        // Unlimited quota - just track for analytics
        if ($limit === -1) {
            return $this->incrementUsageUnlimited($quotaCode, $amount);
        }

        // Zero limit means feature is disabled
        if ($limit === 0) {
            logger()->debug('Quota tracking rejected - zero limit', [
                'tenant_id' => $this->id,
                'quota_code' => $quotaCode,
            ]);
            return false;
        }

        return $this->incrementUsageWithLimit($quotaCode, $amount, $limit);
    }

    /**
     * Atomically increment usage with limit enforcement.
     *
     * Uses a single atomic UPDATE statement with WHERE clause
     * to prevent race conditions.
     *
     * @param string $quotaCode The quota code
     * @param int $amount The amount to increment
     * @param int $limit The quota limit
     * @return bool True if incremented, false if would exceed limit
     */
    protected function incrementUsageWithLimit(string $quotaCode, int $amount, int $limit): bool
    {
        return DB::transaction(function () use ($quotaCode, $amount, $limit) {
            // Ensure usage record exists (with lock to prevent duplicates)
            $usage = $this->usages()
                ->where('quota_code', $quotaCode)
                ->lockForUpdate()
                ->first();

            if (!$usage) {
                // Create initial usage record if it doesn't exist
                // Check if we can create with the initial amount
                if ($amount > $limit) {
                    return false;
                }

                $this->usages()->create([
                    'quota_code' => $quotaCode,
                    'current_usage' => $amount,
                    'reset_at' => $this->calculateNextResetDate($quotaCode),
                ]);

                logger()->debug('Quota usage created', [
                    'tenant_id' => $this->id,
                    'quota_code' => $quotaCode,
                    'usage' => $amount,
                    'limit' => $limit,
                ]);

                return true;
            }

            // Check if period reset is needed
            if ($this->shouldResetUsage($quotaCode, $usage)) {
                $usage->current_usage = 0;
                $usage->reset_at = $this->calculateNextResetDate($quotaCode);
            }

            // Atomic increment with limit check
            // This WHERE clause ensures we only increment if we won't exceed the limit
            $affected = DB::table('tenant_usage')
                ->where('id', $usage->id)
                ->where('tenant_id', $this->id)
                ->whereRaw('(current_usage + ?) <= ?', [$amount, $limit])
                ->update([
                    'current_usage' => DB::raw("current_usage + {$amount}"),
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                logger()->debug('Quota limit reached', [
                    'tenant_id' => $this->id,
                    'quota_code' => $quotaCode,
                    'attempted_amount' => $amount,
                    'limit' => $limit,
                ]);
                return false;
            }

            logger()->debug('Quota usage incremented', [
                'tenant_id' => $this->id,
                'quota_code' => $quotaCode,
                'amount' => $amount,
                'new_usage' => $usage->current_usage + $amount,
                'limit' => $limit,
            ]);

            return true;
        });
    }

    /**
     * Increment usage for unlimited quotas (for analytics only).
     *
     * @param string $quotaCode The quota code
     * @param int $amount The amount to increment
     * @return bool Always returns true for unlimited quotas
     */
    protected function incrementUsageUnlimited(string $quotaCode, int $amount): bool
    {
        $this->usages()->updateOrCreate(
            ['quota_code' => $quotaCode],
            []
        )->increment('current_usage', $amount);

        return true;
    }

    /**
     * Decrement usage for a quota (e.g., when deleting a resource).
     *
     * @param string $quotaCode The quota code
     * @param int $amount The amount to decrement (default 1)
     * @return bool True if decremented successfully
     */
    public function decrementUsage(string $quotaCode, int $amount = 1): bool
    {
        $affected = DB::table('tenant_usage')
            ->where('tenant_id', $this->id)
            ->where('quota_code', $quotaCode)
            ->where('current_usage', '>=', $amount)
            ->update([
                'current_usage' => DB::raw("current_usage - {$amount}"),
                'updated_at' => now(),
            ]);

        return $affected > 0;
    }

    /**
     * Reset usage for a specific quota.
     *
     * @param string $quotaCode The quota code to reset
     * @return bool True if reset successfully
     */
    public function resetUsage(string $quotaCode): bool
    {
        $affected = $this->usages()
            ->where('quota_code', $quotaCode)
            ->update([
                'current_usage' => 0,
                'reset_at' => $this->calculateNextResetDate($quotaCode),
            ]);

        if ($affected > 0) {
            logger()->info('Quota usage reset', [
                'tenant_id' => $this->id,
                'quota_code' => $quotaCode,
            ]);
        }

        return $affected > 0;
    }

    /**
     * Reset all periodic quotas (called on billing cycle renewal).
     *
     * @return int Number of quotas reset
     */
    public function resetAllPeriodicQuotas(): int
    {
        $count = 0;

        $this->quotas()
            ->where('period', '!=', QuotaPeriod::LIFETIME->value)
            ->each(function ($quota) use (&$count) {
                if ($this->resetUsage($quota->quota_code)) {
                    $count++;
                }
            });

        logger()->info('All periodic quotas reset', [
            'tenant_id' => $this->id,
            'count' => $count,
        ]);

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if usage should be reset based on period.
     *
     * @param string $quotaCode The quota code
     * @param TenantUsage $usage The usage record
     * @return bool True if usage should be reset
     */
    protected function shouldResetUsage(string $quotaCode, TenantUsage $usage): bool
    {
        $period = $this->getQuotaPeriod($quotaCode);

        if (!$period || $period === QuotaPeriod::LIFETIME) {
            return false;
        }

        return $period->shouldReset($usage->reset_at);
    }

    /**
     * Calculate the next reset date for a quota.
     *
     * @param string $quotaCode The quota code
     * @return Carbon|null The next reset date, or null for lifetime
     */
    protected function calculateNextResetDate(string $quotaCode): ?Carbon
    {
        $period = $this->getQuotaPeriod($quotaCode);

        if (!$period) {
            return null;
        }

        return $period->getNextResetDate();
    }

    /**
     * Get a summary of all quota usage for the tenant.
     *
     * @return array Array of quota summaries
     */
    public function getQuotaSummary(): array
    {
        $summary = [];

        $this->quotas()->each(function ($quota) use (&$summary) {
            $usage = $this->getUsage($quota->quota_code);
            $limit = $quota->limit;

            $summary[$quota->quota_code] = [
                'code' => $quota->quota_code,
                'usage' => $usage,
                'limit' => $limit,
                'remaining' => $limit === -1 ? 'unlimited' : max(0, $limit - $usage),
                'percentage' => $limit > 0 ? round(($usage / $limit) * 100, 1) : 0,
                'period' => $quota->period,
                'is_unlimited' => $limit === -1,
                'is_exceeded' => $limit !== -1 && $usage >= $limit,
            ];
        });

        return $summary;
    }
}
