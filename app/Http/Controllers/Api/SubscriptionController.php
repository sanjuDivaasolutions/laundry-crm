<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Billing\StripeService;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages tenant subscriptions.
 *
 * Provides endpoints for:
 * - Viewing subscription status
 * - Changing plans
 * - Canceling subscriptions
 * - Resuming canceled subscriptions
 */
class SubscriptionController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected TenantService $tenantService
    ) {}

    /**
     * Get current subscription status.
     *
     * GET /api/subscription
     */
    public function show(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $status = $this->stripeService->getSubscriptionStatus($tenant);

        // Get current plan info if subscribed
        $planInfo = null;
        if ($status['has_subscription']) {
            $subscription = $tenant->subscription('default');
            if ($subscription) {
                $plan = Plan::where('stripe_price_id', $subscription->stripe_price)
                    ->orWhere('stripe_yearly_price_id', $subscription->stripe_price)
                    ->first();

                if ($plan) {
                    $planInfo = $plan->toApiArray();
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $status,
                'plan' => $planInfo,
                'quotas' => $tenant->getQuotaSummary(),
            ],
        ]);
    }

    /**
     * Change subscription plan.
     *
     * POST /api/subscription/change-plan
     */
    public function changePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'exists:plans,code'],
            'billing_period' => ['nullable', 'string', 'in:monthly,yearly'],
            'prorate' => ['nullable', 'boolean'],
        ]);

        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscription = $tenant->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription to change.',
            ], 400);
        }

        $plan = Plan::where('code', $validated['plan_code'])
            ->where('is_active', true)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found or inactive.',
            ], 404);
        }

        try {
            $this->stripeService->changePlan(
                $tenant,
                $plan,
                $validated['billing_period'] ?? 'monthly',
                $validated['prorate'] ?? true
            );

            return response()->json([
                'success' => true,
                'message' => 'Plan changed successfully.',
                'data' => [
                    'new_plan' => $plan->toApiArray(),
                    'subscription' => $this->stripeService->getSubscriptionStatus($tenant),
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Plan change failed', [
                'tenant_id' => $tenant->id,
                'plan_code' => $validated['plan_code'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to change plan.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Cancel subscription.
     *
     * POST /api/subscription/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'immediately' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found.',
            ], 400);
        }

        if ($subscription->canceled() && !$subscription->onGracePeriod()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is already canceled.',
            ], 400);
        }

        try {
            $immediately = $validated['immediately'] ?? false;

            $this->stripeService->cancelSubscription($tenant, $immediately);

            // Log cancellation reason if provided
            if (!empty($validated['reason'])) {
                logger()->info('Subscription canceled with reason', [
                    'tenant_id' => $tenant->id,
                    'reason' => $validated['reason'],
                    'immediately' => $immediately,
                ]);
            }

            $message = $immediately
                ? 'Subscription canceled immediately.'
                : 'Subscription will be canceled at the end of the billing period.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'subscription' => $this->stripeService->getSubscriptionStatus($tenant),
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Subscription cancellation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Resume a canceled subscription (during grace period).
     *
     * POST /api/subscription/resume
     */
    public function resume(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found.',
            ], 400);
        }

        if (!$subscription->onGracePeriod()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription cannot be resumed. Grace period has ended or subscription was not canceled.',
            ], 400);
        }

        try {
            $this->stripeService->resumeSubscription($tenant);

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully.',
                'data' => [
                    'subscription' => $this->stripeService->getSubscriptionStatus($tenant),
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Subscription resume failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resume subscription.',
            ], 500);
        }
    }
}
