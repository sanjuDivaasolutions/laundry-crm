<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

/**
 * QuotaService
 *
 * Centralized service for checking and enforcing tenant resource quotas.
 * Provides caching for performance and comprehensive limit checking.
 */
class QuotaService
{
    /**
     * Cache TTL for quota checks (in seconds).
     */
    private const CACHE_TTL = 60;

    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Check if tenant can create more of a resource.
     *
     * @param string $resource Resource type (items, users, etc.)
     * @param int $amount Amount to add (default 1)
     * @param Tenant|null $tenant Specific tenant or current context
     * @return bool True if allowed, false if would exceed limit
     */
    public function canCreate(string $resource, int $amount = 1, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->tenantService->getTenant();

        if (!$tenant) {
            return true; // No tenant context, allow (handled elsewhere)
        }

        $limit = $tenant->getResourceLimit($resource);

        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        $currentUsage = $this->getCachedUsage($tenant, $resource);

        return ($currentUsage + $amount) <= $limit;
    }

    /**
     * Check multiple resources at once.
     *
     * @param array $resources Array of [resource => amount] pairs
     * @param Tenant|null $tenant Specific tenant or current context
     * @return array Array of resource => bool (true if allowed)
     */
    public function canCreateMultiple(array $resources, ?Tenant $tenant = null): array
    {
        $results = [];

        foreach ($resources as $resource => $amount) {
            $results[$resource] = $this->canCreate($resource, $amount, $tenant);
        }

        return $results;
    }

    /**
     * Get cached resource usage to improve performance.
     */
    protected function getCachedUsage(Tenant $tenant, string $resource): int
    {
        $cacheKey = "tenant:{$tenant->id}:usage:{$resource}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant, $resource) {
            return $tenant->getResourceUsage($resource);
        });
    }

    /**
     * Invalidate cached usage after a resource is created/deleted.
     */
    public function invalidateUsageCache(string $resource, ?Tenant $tenant = null): void
    {
        $tenant = $tenant ?? $this->tenantService->getTenant();

        if ($tenant) {
            $cacheKey = "tenant:{$tenant->id}:usage:{$resource}";
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get quota status for a resource.
     */
    public function getQuotaStatus(string $resource, ?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? $this->tenantService->getTenant();

        if (!$tenant) {
            return [
                'available' => true,
                'usage' => 0,
                'limit' => -1,
                'percentage' => 0,
                'message' => null,
            ];
        }

        $usage = $this->getCachedUsage($tenant, $resource);
        $limit = $tenant->getResourceLimit($resource);
        $percentage = $tenant->getResourceUsagePercentage($resource);

        $available = $limit === -1 || $usage < $limit;
        $message = null;

        if (!$available) {
            $message = "You've reached your {$resource} limit of {$limit}.";
        } elseif ($percentage >= 90) {
            $remaining = $limit - $usage;
            $message = "You're approaching your {$resource} limit. {$remaining} remaining.";
        }

        return [
            'available' => $available,
            'usage' => $usage,
            'limit' => $limit,
            'percentage' => $percentage,
            'message' => $message,
        ];
    }

    /**
     * Get all quota statuses for the tenant.
     */
    public function getAllQuotaStatuses(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? $this->tenantService->getTenant();

        if (!$tenant) {
            return [];
        }

        $plan = $tenant->getCurrentPlan();
        $limits = $plan['limits'] ?? [];

        $statuses = [];
        foreach (array_keys($limits) as $resource) {
            $statuses[$resource] = $this->getQuotaStatus($resource, $tenant);
        }

        return $statuses;
    }

    /**
     * Check if tenant has a specific feature enabled.
     */
    public function hasFeature(string $feature, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->tenantService->getTenant();

        if (!$tenant) {
            return false;
        }

        return $tenant->hasFeature($feature);
    }

    /**
     * Get warning message if approaching limits.
     */
    public function getWarnings(?Tenant $tenant = null): array
    {
        $warnings = [];
        $statuses = $this->getAllQuotaStatuses($tenant);

        foreach ($statuses as $resource => $status) {
            if ($status['message'] && $status['percentage'] >= 80) {
                $warnings[] = [
                    'resource' => $resource,
                    'message' => $status['message'],
                    'percentage' => $status['percentage'],
                    'severity' => $status['percentage'] >= 100 ? 'critical' : ($status['percentage'] >= 90 ? 'warning' : 'info'),
                ];
            }
        }

        return $warnings;
    }

    /**
     * Map resource type to model for automatic quota invalidation.
     */
    public static function getResourceForModel(string $modelClass): ?string
    {
        $mapping = [
            \App\Models\Item::class => 'items',
            \App\Models\User::class => 'users',
            \App\Models\Customer::class => 'customers',
            \App\Models\Order::class => 'orders_per_month',
            \App\Models\Category::class => 'categories',
            \App\Models\Service::class => 'services',
            \App\Models\Payment::class => 'payments',
            \App\Models\Role::class => 'roles',
            \App\Models\Company::class => 'companies',
        ];

        return $mapping[$modelClass] ?? null;
    }
}
