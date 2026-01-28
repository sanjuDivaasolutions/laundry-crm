<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * TenantApiController
 *
 * Admin API for managing tenants (super-admin only).
 */
class TenantApiController extends Controller
{
    /**
     * List all tenants with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage-tenants');

        $query = Tenant::query()
            ->withCount(['users', 'companies'])
            ->with(['subscriptions' => function ($q) {
                $q->active();
            }]);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            match ($request->input('status')) {
                'active' => $query->where('active', true),
                'inactive' => $query->where('active', false),
                'trial' => $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now())
                    ->whereDoesntHave('subscriptions', fn($q) => $q->active()),
                'expired_trial' => $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '<', now())
                    ->whereDoesntHave('subscriptions', fn($q) => $q->active()),
                'subscribed' => $query->whereHas('subscriptions', fn($q) => $q->active()),
                'grace_period' => $query->whereNotNull('grace_period_ends_at')
                    ->where('grace_period_ends_at', '>', now()),
                default => null,
            };
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        $tenants = $query->paginate($request->input('per_page', 15));

        // Transform data
        $tenants->getCollection()->transform(function ($tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'url' => $tenant->getUrl(),
                'active' => $tenant->active,
                'status' => $this->getTenantStatus($tenant),
                'status_label' => $this->getTenantStatusLabel($tenant),
                'trial_ends_at' => $tenant->trial_ends_at?->toIso8601String(),
                'trial_days_remaining' => $tenant->trialDaysRemaining(),
                'grace_period_ends_at' => $tenant->grace_period_ends_at?->toIso8601String(),
                'users_count' => $tenant->users_count,
                'companies_count' => $tenant->companies_count,
                'has_subscription' => $tenant->subscriptions->isNotEmpty(),
                'current_plan' => $tenant->getCurrentPlanCode(),
                'created_at' => $tenant->created_at->toIso8601String(),
            ];
        });

        return response()->json($tenants);
    }

    /**
     * Get a single tenant's details.
     */
    public function show(Tenant $tenant): JsonResponse
    {
        $this->authorize('manage-tenants');

        $tenant->load(['users', 'companies', 'subscriptions']);

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'url' => $tenant->getUrl(),
                'active' => $tenant->active,
                'status' => $this->getTenantStatus($tenant),
                'status_label' => $this->getTenantStatusLabel($tenant),
                'trial_ends_at' => $tenant->trial_ends_at?->toIso8601String(),
                'trial_days_remaining' => $tenant->trialDaysRemaining(),
                'grace_period_ends_at' => $tenant->grace_period_ends_at?->toIso8601String(),
                'suspended_at' => $tenant->suspended_at?->toIso8601String(),
                'suspension_reason' => $tenant->suspension_reason,
                'timezone' => $tenant->timezone,
                'currency' => $tenant->currency,
                'settings' => $tenant->settings,
                'stripe_id' => $tenant->stripe_id,
                'current_plan' => $tenant->getCurrentPlanCode(),
                'created_at' => $tenant->created_at->toIso8601String(),
                'updated_at' => $tenant->updated_at->toIso8601String(),
            ],
            'users' => $tenant->users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ]),
            'companies' => $tenant->companies->map(fn($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
                'active' => $company->active,
            ]),
            'subscriptions' => $tenant->subscriptions->map(fn($sub) => [
                'id' => $sub->id,
                'name' => $sub->name,
                'stripe_status' => $sub->stripe_status,
                'stripe_price' => $sub->stripe_price,
                'quantity' => $sub->quantity,
                'trial_ends_at' => $sub->trial_ends_at?->toIso8601String(),
                'ends_at' => $sub->ends_at?->toIso8601String(),
                'created_at' => $sub->created_at->toIso8601String(),
            ]),
            'usage' => [
                'users' => $tenant->users()->count(),
                'items' => $tenant->items()->count(),
                'orders' => $tenant->orders()->count(),
                'customers' => $tenant->customers()->count(),
            ],
        ]);
    }

    /**
     * Update a tenant.
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('manage-tenants');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'timezone' => ['sometimes', 'timezone'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'settings' => ['sometimes', 'array'],
        ]);

        $tenant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully.',
            'tenant' => $tenant->fresh(),
        ]);
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('manage-tenants');

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $tenant->suspend($request->input('reason'));

        logger()->warning('Tenant suspended by admin', [
            'tenant_id' => $tenant->id,
            'admin_id' => auth()->id(),
            'reason' => $request->input('reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant suspended successfully.',
        ]);
    }

    /**
     * Reactivate a suspended tenant.
     */
    public function reactivate(Tenant $tenant): JsonResponse
    {
        $this->authorize('manage-tenants');

        $tenant->reactivate();

        logger()->info('Tenant reactivated by admin', [
            'tenant_id' => $tenant->id,
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant reactivated successfully.',
        ]);
    }

    /**
     * Extend a tenant's trial period.
     */
    public function extendTrial(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('manage-tenants');

        $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        $days = $request->input('days');
        $currentTrialEnd = $tenant->trial_ends_at ?? now();
        $newTrialEnd = $currentTrialEnd->addDays($days);

        $tenant->update([
            'trial_ends_at' => $newTrialEnd,
        ]);

        logger()->info('Tenant trial extended by admin', [
            'tenant_id' => $tenant->id,
            'admin_id' => auth()->id(),
            'days_added' => $days,
            'new_trial_end' => $newTrialEnd->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Trial extended by {$days} days.",
            'trial_ends_at' => $newTrialEnd->toIso8601String(),
        ]);
    }

    /**
     * Impersonate a tenant (for debugging).
     */
    public function impersonate(Tenant $tenant): JsonResponse
    {
        $this->authorize('impersonate-tenant');

        // Find an admin user for this tenant
        $adminUser = $tenant->users()
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->first();

        if (!$adminUser) {
            return response()->json([
                'success' => false,
                'message' => 'No admin user found for this tenant.',
            ], 404);
        }

        // Generate impersonation token
        $token = auth()->guard('api')->claims([
            'impersonating' => true,
            'impersonated_by' => auth()->id(),
            'original_tenant_id' => auth()->user()->tenant_id ?? null,
        ])->login($adminUser);

        logger()->warning('Admin impersonating tenant', [
            'admin_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'impersonated_user_id' => $adminUser->id,
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'tenant_url' => $tenant->getUrl(),
            'user' => [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
            ],
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('manage-tenants');

        $now = now();

        return response()->json([
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('active', true)->count(),
            'tenants_on_trial' => Tenant::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', $now)
                ->whereDoesntHave('subscriptions', fn($q) => $q->active())
                ->count(),
            'expired_trials' => Tenant::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '<', $now)
                ->whereDoesntHave('subscriptions', fn($q) => $q->active())
                ->count(),
            'paying_tenants' => Tenant::whereHas('subscriptions', fn($q) => $q->active())->count(),
            'in_grace_period' => Tenant::whereNotNull('grace_period_ends_at')
                ->where('grace_period_ends_at', '>', $now)
                ->count(),
            'suspended_tenants' => Tenant::where('active', false)->count(),
            'signups_this_week' => Tenant::where('created_at', '>=', $now->copy()->subWeek())->count(),
            'signups_this_month' => Tenant::where('created_at', '>=', $now->copy()->startOfMonth())->count(),
            'total_users' => User::count(),
        ]);
    }

    /**
     * Get tenant status string.
     */
    protected function getTenantStatus(Tenant $tenant): string
    {
        if (!$tenant->active) {
            return 'suspended';
        }

        if ($tenant->grace_period_ends_at?->isFuture()) {
            return 'grace_period';
        }

        if ($tenant->hasActiveSubscription()) {
            return 'active';
        }

        if ($tenant->onTrial()) {
            return 'trial';
        }

        if ($tenant->trialExpired()) {
            return 'expired';
        }

        return 'inactive';
    }

    /**
     * Get tenant status label for display.
     */
    protected function getTenantStatusLabel(Tenant $tenant): string
    {
        return match ($this->getTenantStatus($tenant)) {
            'active' => 'Active',
            'trial' => 'Trial',
            'expired' => 'Trial Expired',
            'grace_period' => 'Grace Period',
            'suspended' => 'Suspended',
            default => 'Inactive',
        };
    }
}
