<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Handles new tenant signup with optional plan selection.
 *
 * Flow:
 * 1. Create tenant, user, and default company
 * 2. If plan selected, redirect to Stripe Checkout
 * 3. On Stripe success, provision plan features
 */
class SignupController extends Controller
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    /**
     * Register a new tenant with user and company.
     *
     * POST /api/signup
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['required', 'string', 'max:255'],
            'plan_code' => ['nullable', 'string', 'exists:plans,code'],
            'billing_period' => ['nullable', 'string', 'in:monthly,yearly'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                // 1. Create tenant
                $tenant = Tenant::create([
                    'name' => $validated['company_name'],
                    'active' => true,
                ]);

                // 2. Create user
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'tenant_id' => $tenant->id,
                ]);

                // 3. Create default company
                $company = Company::create([
                    'name' => $validated['company_name'],
                    'tenant_id' => $tenant->id,
                ]);

                // Associate user with company
                if (method_exists($user, 'companies')) {
                    $user->companies()->attach($company->id);
                }

                return [
                    'tenant' => $tenant,
                    'user' => $user,
                    'company' => $company,
                ];
            });

            $tenant = $result['tenant'];
            $user = $result['user'];

            // 4. If plan selected, create checkout session
            $checkoutUrl = null;
            if (!empty($validated['plan_code'])) {
                $plan = Plan::where('code', $validated['plan_code'])
                    ->where('is_active', true)
                    ->first();

                if ($plan) {
                    $billingPeriod = $validated['billing_period'] ?? 'monthly';
                    $session = $this->stripeService->createCheckoutSession(
                        $tenant,
                        $plan,
                        $billingPeriod
                    );
                    $checkoutUrl = $session->url;
                }
            }

            // Generate auth token for the new user
            $token = auth()->login($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                    ],
                    'token' => $token,
                    'checkout_url' => $checkoutUrl,
                ],
                'message' => $checkoutUrl
                    ? 'Account created. Complete payment to activate subscription.'
                    : 'Account created successfully.',
            ], 201);

        } catch (\Exception $e) {
            logger()->error('Signup failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Handle successful Stripe checkout.
     *
     * GET /api/checkout/success
     */
    public function checkoutSuccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string'],
        ]);

        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $session = $stripe->checkout->sessions->retrieve($validated['session_id'], [
                'expand' => ['subscription', 'customer'],
            ]);

            $tenantId = $session->metadata->tenant_id ?? null;
            $planCode = $session->subscription->metadata->plan_code ?? null;

            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid checkout session.',
                ], 400);
            }

            $tenant = Tenant::find($tenantId);
            $plan = Plan::where('code', $planCode)->first();

            if ($tenant && $plan) {
                // Provision plan features and quotas
                $plan->provisionForTenant($tenant);

                logger()->info('Checkout completed, plan provisioned', [
                    'tenant_id' => $tenant->id,
                    'plan_code' => $plan->code,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully.',
                'data' => [
                    'tenant_id' => $tenantId,
                    'plan_code' => $planCode,
                    'subscription_id' => $session->subscription->id ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Checkout success handling failed', [
                'session_id' => $validated['session_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to activate subscription.',
            ], 500);
        }
    }

    /**
     * Get available plans for signup.
     *
     * GET /api/plans
     */
    public function getPlans(): JsonResponse
    {
        $plans = Plan::active()
            ->ordered()
            ->with(['features', 'quotas'])
            ->get()
            ->map(fn ($plan) => $plan->toApiArray());

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }
}
