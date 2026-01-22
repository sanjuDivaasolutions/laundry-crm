<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Billing\StripeService;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles billing-related operations.
 *
 * Provides endpoints for:
 * - Accessing Stripe Billing Portal
 * - Viewing invoices
 * - Updating payment methods
 * - Viewing upcoming invoices
 */
class BillingController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected TenantService $tenantService
    ) {}

    /**
     * Get billing overview.
     *
     * GET /api/billing
     */
    public function index(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $subscriptionStatus = $this->stripeService->getSubscriptionStatus($tenant);
        $upcomingInvoice = $this->stripeService->getUpcomingInvoice($tenant);
        $recentInvoices = $this->stripeService->getInvoices($tenant, 5);

        // Get payment method info
        $paymentMethod = null;
        if ($tenant->hasStripeId() && $tenant->hasDefaultPaymentMethod()) {
            $pm = $tenant->defaultPaymentMethod();
            $paymentMethod = [
                'brand' => $pm->card->brand ?? null,
                'last4' => $pm->card->last4 ?? null,
                'exp_month' => $pm->card->exp_month ?? null,
                'exp_year' => $pm->card->exp_year ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscriptionStatus,
                'payment_method' => $paymentMethod,
                'upcoming_invoice' => $upcomingInvoice,
                'recent_invoices' => $recentInvoices,
                'quotas' => $tenant->getQuotaSummary(),
            ],
        ]);
    }

    /**
     * Create a Stripe Billing Portal session and redirect.
     *
     * GET /api/billing/portal
     */
    public function portal(Request $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        if (!$tenant->hasStripeId()) {
            return response()->json([
                'success' => false,
                'message' => 'No billing account found. Please subscribe to a plan first.',
            ], 400);
        }

        try {
            $returnUrl = $request->query('return_url');
            $session = $this->stripeService->createBillingPortalSession(
                $tenant,
                $returnUrl
            );

            // If this is an API request, return the URL
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'portal_url' => $session->url,
                    ],
                ]);
            }

            // Otherwise redirect directly
            return redirect($session->url);

        } catch (\Exception $e) {
            logger()->error('Billing portal session creation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create billing portal session.',
            ], 500);
        }
    }

    /**
     * Get all invoices.
     *
     * GET /api/billing/invoices
     */
    public function invoices(Request $request): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $limit = min((int) $request->query('limit', 20), 100);
        $invoices = $this->stripeService->getInvoices($tenant, $limit);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get upcoming invoice preview.
     *
     * GET /api/billing/upcoming
     */
    public function upcomingInvoice(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        $upcoming = $this->stripeService->getUpcomingInvoice($tenant);

        if (!$upcoming) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No upcoming invoice.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $upcoming,
        ]);
    }

    /**
     * Update payment method (setup intent flow).
     *
     * POST /api/billing/payment-method
     */
    public function updatePaymentMethod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'string'],
        ]);

        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        if (!$tenant->hasStripeId()) {
            return response()->json([
                'success' => false,
                'message' => 'No billing account found.',
            ], 400);
        }

        try {
            $success = $this->stripeService->updatePaymentMethod(
                $tenant,
                $validated['payment_method_id']
            );

            if ($success) {
                $pm = $tenant->defaultPaymentMethod();
                return response()->json([
                    'success' => true,
                    'message' => 'Payment method updated successfully.',
                    'data' => [
                        'brand' => $pm->card->brand ?? null,
                        'last4' => $pm->card->last4 ?? null,
                        'exp_month' => $pm->card->exp_month ?? null,
                        'exp_year' => $pm->card->exp_year ?? null,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method.',
            ], 500);

        } catch (\Exception $e) {
            logger()->error('Payment method update failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a setup intent for adding a new payment method.
     *
     * POST /api/billing/setup-intent
     */
    public function createSetupIntent(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found.',
            ], 400);
        }

        if (!$tenant->hasStripeId()) {
            // Create customer first
            $this->stripeService->createCustomer($tenant);
        }

        try {
            $intent = $tenant->createSetupIntent();

            return response()->json([
                'success' => true,
                'data' => [
                    'client_secret' => $intent->client_secret,
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Setup intent creation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create setup intent.',
            ], 500);
        }
    }
}
