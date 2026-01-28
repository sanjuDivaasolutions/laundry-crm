<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforceTenantQuota Middleware
 *
 * Enforces subscription status, trial expiration, and resource quotas.
 *
 * Design Decisions (from interview):
 * - Trial: 14-day full access, then read-only
 * - Grace period: 7 days after payment failure
 * - Plan-based limits: Different limits per plan
 * - Global rate limits: Same for all tenants
 *
 * Usage:
 * - Route::middleware('quota') - Check subscription status only
 * - Route::middleware('quota:items') - Check subscription + items limit
 * - Route::middleware('quota:orders_per_month') - Check subscription + monthly orders
 */
class EnforceTenantQuota
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $resource Resource to check quota for (e.g., 'items', 'orders_per_month')
     * @param int $amount Amount to add (default 1 for create operations)
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $resource = null, int $amount = 1): Response
    {
        $tenant = $this->tenantService->getTenant();

        // No tenant context - let IdentifyTenant handle this
        if (!$tenant) {
            return $next($request);
        }

        // 1. Check if tenant is suspended
        if ($tenant->isSuspended()) {
            return $this->suspendedResponse($tenant->suspension_reason);
        }

        // 2. Check if tenant is active
        if (!$tenant->active) {
            return $this->inactiveResponse();
        }

        // 3. Check trial expiration
        if ($tenant->trialExpired()) {
            // Trial expired, check if they have subscription
            if (!$tenant->hasActiveSubscription()) {
                // No subscription - read-only mode
                if ($this->isMutatingRequest($request)) {
                    return $this->trialExpiredResponse($tenant);
                }
                // Allow read operations
            }
        }

        // 4. Check grace period status
        if ($tenant->isInGracePeriod()) {
            // Show warning header but allow access
            $response = $next($request);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);
                $data['_warnings'] = $data['_warnings'] ?? [];
                $data['_warnings'][] = [
                    'type' => 'payment_required',
                    'message' => 'Payment is past due. Please update your payment method.',
                    'days_remaining' => $tenant->gracePeriodDaysRemaining(),
                ];
                $response->setData($data);
            }

            return $response;
        }

        // 5. Check resource quota (if specified)
        if ($resource && $this->isMutatingRequest($request)) {
            if ($tenant->hasReachedLimit($resource)) {
                return $this->quotaExceededResponse($resource, $tenant);
            }
        }

        // 6. Check trial warning
        $response = $next($request);

        if ($tenant->shouldShowTrialWarning() && $response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            $data['_warnings'] = $data['_warnings'] ?? [];
            $data['_warnings'][] = [
                'type' => 'trial_ending',
                'message' => "Your trial ends in {$tenant->trialDaysRemaining()} days.",
                'days_remaining' => $tenant->trialDaysRemaining(),
                'upgrade_url' => route('api.billing.subscribe'),
            ];
            $response->setData($data);
        }

        return $response;
    }

    /**
     * Check if request is a mutating operation (create, update, delete).
     */
    protected function isMutatingRequest(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Response for suspended tenant.
     */
    protected function suspendedResponse(?string $reason): Response
    {
        return response()->json([
            'success' => false,
            'error' => 'account_suspended',
            'message' => 'Your account has been suspended. Please contact support.',
            'reason' => $reason,
            'support_url' => config('app.support_url', 'mailto:support@laundry-crm.com'),
        ], 403);
    }

    /**
     * Response for inactive tenant.
     */
    protected function inactiveResponse(): Response
    {
        return response()->json([
            'success' => false,
            'error' => 'account_inactive',
            'message' => 'Your account is not active. Please contact support.',
        ], 403);
    }

    /**
     * Response for expired trial.
     */
    protected function trialExpiredResponse($tenant): Response
    {
        return response()->json([
            'success' => false,
            'error' => 'trial_expired',
            'message' => 'Your trial has expired. Subscribe to continue creating and editing.',
            'trial_ended_at' => $tenant->trial_ends_at->toIso8601String(),
            'read_only' => true,
            'upgrade_url' => route('api.billing.subscribe'),
            'plans' => $this->getAvailablePlans(),
        ], 402);
    }

    /**
     * Response for exceeded quota.
     */
    protected function quotaExceededResponse(string $resource, $tenant): Response
    {
        $limit = $tenant->getResourceLimit($resource);
        $usage = $tenant->getResourceUsage($resource);
        $plan = $tenant->getCurrentPlanCode();

        // Find next plan with higher limit
        $upgradePlan = $this->findUpgradePlan($resource, $limit);

        return response()->json([
            'success' => false,
            'error' => 'quota_exceeded',
            'message' => "You've reached your {$resource} limit. Please upgrade your plan.",
            'resource' => $resource,
            'current_usage' => $usage,
            'limit' => $limit,
            'current_plan' => $plan,
            'upgrade_plan' => $upgradePlan,
            'upgrade_url' => route('api.billing.upgrade'),
        ], 402);
    }

    /**
     * Get available subscription plans.
     */
    protected function getAvailablePlans(): array
    {
        $plans = config('tenancy.plans', []);

        return collect($plans)->map(function ($plan, $code) {
            return [
                'code' => $code,
                'name' => $plan['name'],
                'price' => $plan['price'] / 100, // Convert cents to dollars
                'users_included' => $plan['users_included'],
                'features' => $plan['features'],
            ];
        })->values()->toArray();
    }

    /**
     * Find a plan to upgrade to based on resource needs.
     */
    protected function findUpgradePlan(string $resource, int $currentLimit): ?array
    {
        $plans = config('tenancy.plans', []);

        foreach ($plans as $code => $plan) {
            $planLimit = $plan['limits'][$resource] ?? 0;

            // -1 means unlimited, or must be higher than current
            if ($planLimit === -1 || $planLimit > $currentLimit) {
                return [
                    'code' => $code,
                    'name' => $plan['name'],
                    'price' => $plan['price'] / 100,
                    'new_limit' => $planLimit === -1 ? 'Unlimited' : $planLimit,
                ];
            }
        }

        return null;
    }
}
