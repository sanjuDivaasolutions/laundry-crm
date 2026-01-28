<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasEntitlements;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;

/**
 * Tenant Model
 *
 * Represents an organization/business using the platform.
 * Each tenant has isolated data, users, and settings.
 *
 * @property int $id
 * @property string $name
 * @property string $domain Subdomain for this tenant
 * @property bool $active Whether tenant can access the system
 * @property string|null $stripe_id Stripe customer ID
 * @property string|null $pm_type Payment method type
 * @property string|null $pm_last_four Last 4 digits of payment method
 * @property Carbon|null $trial_ends_at Trial expiration date
 * @property Carbon|null $grace_period_ends_at Grace period expiration (after payment failure)
 * @property Carbon|null $suspended_at When tenant was suspended
 * @property string|null $suspension_reason Reason for suspension
 * @property string $timezone Tenant's timezone
 * @property string $currency Tenant's currency (3-letter code)
 * @property string|null $logo_path Path to tenant's logo
 * @property array|null $settings JSON settings storage
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
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
        'grace_period_ends_at',
        'suspended_at',
        'suspension_reason',
        'timezone',
        'currency',
        'logo_path',
        'settings',
    ];

    protected $casts = [
        'active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'active' => true,
        'timezone' => 'UTC',
        'currency' => 'USD',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Users belonging to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Companies belonging to this tenant.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Items belonging to this tenant.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Categories belonging to this tenant.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Settings for this tenant.
     */
    public function tenantSettings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    /**
     * Customers belonging to this tenant.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Orders belonging to this tenant.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Services belonging to this tenant.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Payments belonging to this tenant.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Roles belonging to this tenant.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get subscriptions (override for Cashier compatibility).
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'tenant_id')
            ->from('tenant_subscriptions');
    }

    // =========================================================================
    // TRIAL PERIOD METHODS
    // =========================================================================

    /**
     * Check if tenant is currently in trial period.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired without active subscription.
     */
    public function trialExpired(): bool
    {
        return $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast()
            && !$this->hasActiveSubscription();
    }

    /**
     * Get remaining trial days.
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Check if trial warning should be shown (< X days remaining).
     */
    public function shouldShowTrialWarning(): bool
    {
        if (!$this->onTrial()) {
            return false;
        }

        $warningDays = config('tenancy.trial.warning_days', 3);

        return $this->trialDaysRemaining() <= $warningDays;
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
     * Start a trial period for this tenant.
     */
    public function startTrial(?int $days = null): self
    {
        $days = $days ?? config('tenancy.trial.days', 14);

        $this->update([
            'trial_ends_at' => now()->addDays($days),
        ]);

        return $this;
    }

    /**
     * Extend the trial period.
     */
    public function extendTrial(int $days): self
    {
        $newEndDate = $this->trial_ends_at
            ? $this->trial_ends_at->addDays($days)
            : now()->addDays($days);

        $this->update([
            'trial_ends_at' => $newEndDate,
        ]);

        return $this;
    }

    // =========================================================================
    // SUBSCRIPTION METHODS
    // =========================================================================

    /**
     * Check if tenant has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscribed('default');
    }

    /**
     * Check if tenant can access the platform.
     * Returns true if on trial, has subscription, or in grace period.
     */
    public function canAccess(): bool
    {
        // Account must be active
        if (!$this->active) {
            return false;
        }

        // Must not be suspended
        if ($this->suspended_at !== null) {
            return false;
        }

        // On trial
        if ($this->onTrial()) {
            return true;
        }

        // Has active subscription
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // In grace period
        if ($this->isInGracePeriod()) {
            return true;
        }

        return false;
    }

    /**
     * Check if tenant is read-only (trial expired, no subscription).
     */
    public function isReadOnly(): bool
    {
        return $this->trialExpired() && !$this->hasActiveSubscription() && !$this->isInGracePeriod();
    }

    /**
     * Get the current plan code from active subscription.
     */
    public function getCurrentPlanCode(): ?string
    {
        // On trial - return trial plan
        if ($this->onTrial() && !$this->hasActiveSubscription()) {
            return 'trial';
        }

        $subscription = $this->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return null;
        }

        // Match Stripe price ID to plan code
        $stripePriceId = $subscription->stripe_price;
        $plans = config('tenancy.plans', []);

        foreach ($plans as $code => $plan) {
            if (($plan['stripe_price_id'] ?? null) === $stripePriceId) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Get the current plan configuration.
     */
    public function getCurrentPlan(): ?array
    {
        $planCode = $this->getCurrentPlanCode();

        if (!$planCode) {
            return null;
        }

        return config("tenancy.plans.{$planCode}");
    }

    // =========================================================================
    // GRACE PERIOD METHODS
    // =========================================================================

    /**
     * Check if tenant is in grace period (payment failed but still allowed access).
     */
    public function isInGracePeriod(): bool
    {
        return $this->grace_period_ends_at !== null && $this->grace_period_ends_at->isFuture();
    }

    /**
     * Get remaining grace period days.
     */
    public function gracePeriodDaysRemaining(): int
    {
        if (!$this->grace_period_ends_at || $this->grace_period_ends_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->grace_period_ends_at);
    }

    /**
     * Start grace period (called when payment fails).
     */
    public function startGracePeriod(?int $days = null): self
    {
        $days = $days ?? config('tenancy.grace_period.days', 7);

        $this->update([
            'grace_period_ends_at' => now()->addDays($days),
        ]);

        return $this;
    }

    /**
     * Clear grace period (called when payment succeeds).
     */
    public function clearGracePeriod(): self
    {
        $this->update([
            'grace_period_ends_at' => null,
        ]);

        return $this;
    }

    // =========================================================================
    // SUSPENSION METHODS
    // =========================================================================

    /**
     * Suspend the tenant.
     */
    public function suspend(string $reason = 'Payment failure'): self
    {
        $this->update([
            'active' => false,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ]);

        logger()->warning('Tenant suspended', [
            'tenant_id' => $this->id,
            'reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Reactivate a suspended tenant.
     */
    public function reactivate(): self
    {
        $this->update([
            'active' => true,
            'suspended_at' => null,
            'suspension_reason' => null,
            'grace_period_ends_at' => null,
        ]);

        logger()->info('Tenant reactivated', [
            'tenant_id' => $this->id,
        ]);

        return $this;
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    // =========================================================================
    // QUOTA & LIMITS METHODS
    // =========================================================================

    /**
     * Get limit for a resource based on current plan.
     */
    public function getResourceLimit(string $resource): int
    {
        $plan = $this->getCurrentPlan();

        if (!$plan) {
            return 0;
        }

        return $plan['limits'][$resource] ?? 0;
    }

    /**
     * Check if a resource limit has been reached.
     */
    public function hasReachedLimit(string $resource): bool
    {
        $limit = $this->getResourceLimit($resource);

        // -1 means unlimited
        if ($limit === -1) {
            return false;
        }

        $currentUsage = $this->getResourceUsage($resource);

        return $currentUsage >= $limit;
    }

    /**
     * Get current usage for a resource.
     *
     * Supported resources:
     * - items: Total items/products
     * - users: Total users
     * - customers: Total customers
     * - orders_per_month: Orders created this month
     * - categories: Total categories
     * - services: Total services
     * - api_calls_per_day: API calls today (from cache)
     */
    public function getResourceUsage(string $resource): int
    {
        return match ($resource) {
            'items' => $this->items()->count(),
            'users' => $this->users()->count(),
            'customers' => $this->customers()->count(),
            'orders_per_month' => $this->getMonthlyOrderCount(),
            'orders_per_day' => $this->getDailyOrderCount(),
            'categories' => $this->categories()->count(),
            'services' => $this->services()->count(),
            'payments' => $this->payments()->count(),
            'roles' => $this->roles()->count(),
            'companies' => $this->companies()->count(),
            'api_calls_per_day' => $this->getDailyApiCallCount(),
            'storage_mb' => $this->getStorageUsageMb(),
            default => 0,
        };
    }

    /**
     * Get order count for current month.
     */
    protected function getMonthlyOrderCount(): int
    {
        return $this->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    /**
     * Get order count for today.
     */
    protected function getDailyOrderCount(): int
    {
        return $this->orders()
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    /**
     * Get API call count for today (from rate limiter cache).
     */
    protected function getDailyApiCallCount(): int
    {
        $cacheKey = "tenant:{$this->id}:api_calls:" . now()->toDateString();
        return (int) cache()->get($cacheKey, 0);
    }

    /**
     * Increment API call count (called from middleware).
     */
    public function incrementApiCallCount(): void
    {
        $cacheKey = "tenant:{$this->id}:api_calls:" . now()->toDateString();
        $ttl = now()->endOfDay()->diffInSeconds(now());
        cache()->increment($cacheKey, 1);
        cache()->put($cacheKey, cache()->get($cacheKey, 1), $ttl);
    }

    /**
     * Get storage usage in MB (approximate based on media library).
     */
    protected function getStorageUsageMb(): int
    {
        // This would typically query Spatie media library or file storage
        // For now, return 0 as placeholder
        return 0;
    }

    /**
     * Get all resource usages for the tenant.
     */
    public function getAllResourceUsages(): array
    {
        $plan = $this->getCurrentPlan();
        $limits = $plan['limits'] ?? [];

        $usages = [];
        foreach (array_keys($limits) as $resource) {
            $usages[$resource] = [
                'current' => $this->getResourceUsage($resource),
                'limit' => $this->getResourceLimit($resource),
                'percentage' => $this->getResourceUsagePercentage($resource),
            ];
        }

        return $usages;
    }

    /**
     * Get resource usage as a percentage of limit.
     */
    public function getResourceUsagePercentage(string $resource): int
    {
        $limit = $this->getResourceLimit($resource);

        if ($limit === -1) {
            return 0; // Unlimited
        }

        if ($limit === 0) {
            return 100;
        }

        $usage = $this->getResourceUsage($resource);
        return min(100, (int) round(($usage / $limit) * 100));
    }

    /**
     * Check if tenant has a specific feature enabled.
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->getCurrentPlan();

        if (!$plan) {
            return false;
        }

        return $plan['features'][$feature] ?? false;
    }

    // =========================================================================
    // SETTINGS HELPERS
    // =========================================================================

    /**
     * Get a setting value for this tenant.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return TenantSetting::getValue($this->id, $key, $default);
    }

    /**
     * Set a setting value for this tenant.
     */
    public function setSetting(string $key, mixed $value, string $type = 'string'): TenantSetting
    {
        return TenantSetting::setValue($this->id, $key, $value, $type);
    }

    /**
     * Get all settings for this tenant.
     */
    public function getAllSettings(?string $group = null): array
    {
        return TenantSetting::getAllForTenant($this->id, $group);
    }

    // =========================================================================
    // URL HELPERS
    // =========================================================================

    /**
     * Get the full URL for this tenant.
     */
    public function getUrl(): string
    {
        $baseDomain = config('tenancy.base_domain', 'localhost');
        $scheme = app()->environment('production') ? 'https' : 'http';

        return "{$scheme}://{$this->domain}.{$baseDomain}";
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to active tenants only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to tenants on trial.
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope to tenants with expired trials.
     */
    public function scopeTrialExpired($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->whereDoesntHave('subscriptions', function ($q) {
                $q->where('stripe_status', 'active');
            });
    }

    /**
     * Scope to suspended tenants.
     */
    public function scopeSuspended($query)
    {
        return $query->whereNotNull('suspended_at');
    }

    /**
     * Scope to tenants in grace period.
     */
    public function scopeInGracePeriod($query)
    {
        return $query->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '>', now());
    }
}
