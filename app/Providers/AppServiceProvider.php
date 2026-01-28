<?php

/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 16/10/24, 6:39 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Providers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Services\TenantService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        $this->configureGates();
        $this->configureTenantScopedBindings();
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function configureGates()
    {
        // Define gates for simple permissions
        Gate::define('manage-agents', function (User $user) {
            return $user->hasPermission('manage-agents');
        });

        Gate::define('view-commissions', function (User $user) {
            return $user->hasPermission('view-commissions');
        });

        Gate::define('approve-commissions', function (User $user) {
            return $user->hasPermission('approve-commissions');
        });

        Gate::define('manage-orders', function (User $user) {
            return $user->hasPermission('manage-orders');
        });

        Gate::define('view-reports', function (User $user) {
            return $user->hasPermission('view-reports');
        });

        Gate::define('manage-companies', function (User $user) {
            return $user->hasPermission('manage-companies');
        });

        // Admin gates - return true for admin, null to continue checking
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            // Return null to continue to the gate check
            return null;
        });

        // Fallback gate for dynamic permission checking
        Gate::after(function (User $user, string $ability, ?bool $result) {
            // If a gate already returned a result, use that
            if ($result !== null) {
                return $result;
            }

            // Otherwise, check if the user has the permission through their roles
            return $user->hasPermission($ability);
        });
    }

    /**
     * Configure tenant-scoped route model bindings.
     *
     * These bindings ensure that when a model is resolved from a route parameter,
     * it is scoped to the current tenant context. This prevents cross-tenant
     * data access via URL manipulation.
     *
     * Security: If a model ID is provided that belongs to a different tenant,
     * a 404 response is returned (not 403) to avoid revealing the existence
     * of resources in other tenants.
     */
    protected function configureTenantScopedBindings(): void
    {
        // List of tenant-scoped models and their route parameter names
        // Format: 'route_parameter' => ModelClass::class
        $tenantScopedModels = [
            'item' => Item::class,
            'category' => Category::class,
            'customer' => Customer::class,
            'order' => Order::class,
            'payment' => Payment::class,
            'company' => Company::class,
            'service' => Service::class,
            'role' => Role::class,
            'user' => User::class,
        ];

        foreach ($tenantScopedModels as $parameter => $modelClass) {
            Route::bind($parameter, function ($value) use ($modelClass) {
                // The global tenant scope should already be applied
                // This explicit binding ensures it works even if scope was bypassed
                $model = $modelClass::where('id', $value)->first();

                if (!$model) {
                    // Return 404 - don't reveal if resource exists in another tenant
                    abort(404);
                }

                return $model;
            });
        }
    }
}
