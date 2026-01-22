<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\StripeService;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles Stripe Checkout session creation and callbacks.
 */
class CheckoutController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected TenantService $tenantService
    ) {}

    /**
     * Create a Stripe Checkout session for subscription.
     *
     * POST /api/checkout
     */
    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'exists:plans,code'],
            'billing_period' => ['nullable', 'string', 'in:monthly,yearly'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ]);

        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
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

        // Check if tenant already has an active subscription
        $existingSubscription = $tenant->subscription('default');
        if ($existingSubscription && $existingSubscription->active()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant already has an active subscription. Use plan change instead.',
                'data' => [
                    'current_status' => $this->stripeService->getSubscriptionStatus($tenant),
                ],
            ], 400);
        }

        try {
            $session = $this->stripeService->createCheckoutSession(
                $tenant,
                $plan,
                $validated['billing_period'] ?? 'monthly',
                $validated['success_url'] ?? null,
                $validated['cancel_url'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Checkout session creation failed', [
                'tenant_id' => $tenant->id,
                'plan_code' => $validated['plan_code'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create checkout session.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Handle successful checkout callback.
     *
     * GET /api/checkout/success
     */
    public function handleSuccess(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing session ID.',
            ], 400);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription', 'subscription.plan'],
            ]);

            $tenantId = $session->metadata->tenant_id ?? null;
            $planCode = $session->subscription?->metadata?->plan_code ?? null;

            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid checkout session.',
                ], 400);
            }

            $tenant = Tenant::find($tenantId);
            $plan = $planCode ? Plan::where('code', $planCode)->first() : null;

            if ($tenant && $plan) {
                // Provision plan features and quotas
                $plan->provisionForTenant($tenant);
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully.',
                'data' => [
                    'subscription_id' => $session->subscription?->id,
                    'plan_code' => $planCode,
                    'status' => $session->subscription?->status,
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Checkout success handling failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify checkout.',
            ], 500);
        }
    }

    /**
     * Handle canceled checkout.
     *
     * GET /api/checkout/cancel
     */
    public function handleCancel(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Checkout canceled.',
        ]);
    }
}
