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

        if (! $tenant) {
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

        return $this->success([
            'subscription' => $status,
            'plan' => $planInfo,
            'quotas' => $tenant->getQuotaSummary(),
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

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscription = $tenant->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return $this->error('No active subscription to change.', 400);
        }

        $plan = Plan::where('code', $validated['plan_code'])
            ->where('is_active', true)
            ->first();

        if (! $plan) {
            return $this->error('Plan not found or inactive.', 404);
        }

        try {
            $this->stripeService->changePlan(
                $tenant,
                $plan,
                $validated['billing_period'] ?? 'monthly',
                $validated['prorate'] ?? true
            );

            return $this->success([
                'new_plan' => $plan->toApiArray(),
                'subscription' => $this->stripeService->getSubscriptionStatus($tenant),
            ], 'Plan changed successfully.');

        } catch (\Exception $e) {
            logger()->error('Plan change failed', [
                'tenant_id' => $tenant->id,
                'plan_code' => $validated['plan_code'],
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to change plan.', 500, config('app.debug') ? $e->getMessage() : null);
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

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscription = $tenant->subscription('default');

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found.',
            ], 400);
        }

        if ($subscription->canceled() && ! $subscription->onGracePeriod()) {
            return $this->error('Subscription is already canceled.', 400);
        }

        try {
            $immediately = $validated['immediately'] ?? false;

            $this->stripeService->cancelSubscription($tenant, $immediately);

            // Log cancellation reason if provided
            if (! empty($validated['reason'])) {
                logger()->info('Subscription canceled with reason', [
                    'tenant_id' => $tenant->id,
                    'reason' => $validated['reason'],
                    'immediately' => $immediately,
                ]);
            }

            $message = $immediately
                ? 'Subscription canceled immediately.'
                : 'Subscription will be canceled at the end of the billing period.';

            return $this->success([
                'subscription' => $this->stripeService->getSubscriptionStatus($tenant),
            ], $message);

        } catch (\Exception $e) {
            logger()->error('Subscription cancellation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to cancel subscription.', 500, config('app.debug') ? $e->getMessage() : null);
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

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscription = $tenant->subscription('default');

        if (! $subscription) {
            return $this->error('No subscription found.', 400);
        }

        if (! $subscription->onGracePeriod()) {
            return $this->error('Subscription cannot be resumed. Grace period has ended or subscription was not canceled.', 400);
        }

        try {
            $this->stripeService->resumeSubscription($tenant);

            return $this->success([
                'subscription' => $this->stripeService->getSubscriptionStatus($tenant),
            ], 'Subscription resumed successfully.');

        } catch (\Exception $e) {
            logger()->error('Subscription resume failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to resume subscription.', 500);
        }
    }
}
