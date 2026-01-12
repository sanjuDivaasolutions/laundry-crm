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

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cashier::useCustomerModel(Buyer::class);
        Cashier::calculateTaxes();

        $this->configureRateLimiting();
        $this->configureGates();
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

        // Admin gates
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
