<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for subscription plans.
 *
 * Public endpoints for displaying pricing page.
 * Admin endpoints for managing plans.
 */
class PlanController extends Controller
{
    /**
     * List all active plans (public endpoint).
     *
     * GET /api/plans
     */
    public function index(): JsonResponse
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

    /**
     * Get a single plan by code (public endpoint).
     *
     * GET /api/plans/{code}
     */
    public function show(string $code): JsonResponse
    {
        $plan = Plan::where('code', $code)
            ->where('is_active', true)
            ->with(['features', 'quotas'])
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan->toApiArray(),
        ]);
    }

    /**
     * Compare plans (public endpoint).
     *
     * GET /api/plans/compare
     */
    public function compare(): JsonResponse
    {
        $plans = Plan::active()
            ->ordered()
            ->with(['features', 'quotas'])
            ->get();

        // Get all unique feature codes across plans
        $allFeatures = $plans->flatMap(fn ($plan) =>
            $plan->features->pluck('feature_code')
        )->unique()->sort()->values();

        // Get all unique quota codes
        $allQuotas = $plans->flatMap(fn ($plan) =>
            $plan->quotas->pluck('quota_code')
        )->unique()->sort()->values();

        // Build comparison matrix
        $comparison = [
            'plans' => $plans->map(fn ($plan) => [
                'code' => $plan->code,
                'name' => $plan->name,
                'price_monthly' => $plan->price_monthly,
                'price_yearly' => $plan->price_yearly,
                'price_monthly_formatted' => $plan->getFormattedPrice('monthly'),
                'price_yearly_formatted' => $plan->getFormattedPrice('yearly'),
                'is_featured' => $plan->is_featured,
            ]),
            'features' => $allFeatures->mapWithKeys(fn ($code) => [
                $code => $plans->mapWithKeys(fn ($plan) => [
                    $plan->code => $plan->hasFeature($code),
                ]),
            ]),
            'quotas' => $allQuotas->mapWithKeys(fn ($code) => [
                $code => $plans->mapWithKeys(fn ($plan) => [
                    $plan->code => $plan->getQuotaLimit($code),
                ]),
            ]),
        ];

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    /**
     * Admin: List all plans including inactive.
     *
     * GET /api/admin/plans
     */
    public function adminIndex(): JsonResponse
    {
        $plans = Plan::ordered()
            ->with(['features', 'quotas'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Admin: Create a new plan.
     *
     * POST /api/admin/plans
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:plans,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stripe_product_id' => ['nullable', 'string', 'max:100'],
            'stripe_price_id' => ['nullable', 'string', 'max:100'],
            'stripe_yearly_price_id' => ['nullable', 'string', 'max:100'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'price_yearly' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'features' => ['nullable', 'array'],
            'features.*.feature_code' => ['required', 'string'],
            'features.*.enabled' => ['nullable', 'boolean'],
            'features.*.config' => ['nullable', 'array'],
            'quotas' => ['nullable', 'array'],
            'quotas.*.quota_code' => ['required', 'string'],
            'quotas.*.limit_value' => ['required', 'integer'],
            'quotas.*.period' => ['nullable', 'string', 'in:lifetime,daily,monthly,yearly'],
        ]);

        $plan = Plan::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'stripe_product_id' => $validated['stripe_product_id'] ?? null,
            'stripe_price_id' => $validated['stripe_price_id'] ?? null,
            'stripe_yearly_price_id' => $validated['stripe_yearly_price_id'] ?? null,
            'price_monthly' => $validated['price_monthly'],
            'price_yearly' => $validated['price_yearly'],
            'currency' => $validated['currency'] ?? 'usd',
            'trial_days' => $validated['trial_days'] ?? 14,
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Create features
        if (!empty($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                $plan->features()->create([
                    'feature_code' => $feature['feature_code'],
                    'enabled' => $feature['enabled'] ?? true,
                    'config' => $feature['config'] ?? null,
                ]);
            }
        }

        // Create quotas
        if (!empty($validated['quotas'])) {
            foreach ($validated['quotas'] as $quota) {
                $plan->quotas()->create([
                    'quota_code' => $quota['quota_code'],
                    'limit_value' => $quota['limit_value'],
                    'period' => $quota['period'] ?? 'monthly',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully.',
            'data' => $plan->load(['features', 'quotas']),
        ], 201);
    }

    /**
     * Admin: Update a plan.
     *
     * PUT /api/admin/plans/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stripe_product_id' => ['nullable', 'string', 'max:100'],
            'stripe_price_id' => ['nullable', 'string', 'max:100'],
            'stripe_yearly_price_id' => ['nullable', 'string', 'max:100'],
            'price_monthly' => ['sometimes', 'integer', 'min:0'],
            'price_yearly' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plan updated successfully.',
            'data' => $plan->fresh()->load(['features', 'quotas']),
        ]);
    }

    /**
     * Admin: Delete a plan.
     *
     * DELETE /api/admin/plans/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $plan = Plan::findOrFail($id);

        // Check if any tenants are using this plan
        // In a real app, you'd prevent deletion if active subscriptions exist

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plan deleted successfully.',
        ]);
    }
}
