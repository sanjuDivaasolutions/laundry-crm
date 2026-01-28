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
 *  *  Last modified: 17/10/24, 2:31 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admin', function () {
    return view('admin');
});

Route::get('/', function () {
    return redirect('/admin');
});

/*
|--------------------------------------------------------------------------
| Pricing & Checkout Routes
|--------------------------------------------------------------------------
*/

// Public pricing page
Route::get('/pricing', function () {
    $plans = Plan::active()->ordered()->with(['features', 'quotas'])->get();
    return view('front-end.pricing', compact('plans'));
})->name('pricing');

// Checkout success - provision plan for tenant
Route::get('/checkout/success', function (Request $request) {
    $sessionId = $request->query('session_id');

    if (!$sessionId) {
        return redirect('/pricing')->with('error', 'Invalid checkout session');
    }

    try {
        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription'],
        ]);

        $tenantId = $session->metadata->tenant_id ?? null;
        $planCode = $session->subscription?->metadata?->plan_code ?? null;

        if ($tenantId && $planCode) {
            $tenant = Tenant::find($tenantId);
            $plan = Plan::where('code', $planCode)->first();

            if ($tenant && $plan) {
                $plan->provisionForTenant($tenant);
            }
        }

        return view('front-end.checkout-success', [
            'subscription_id' => $session->subscription?->id,
        ]);
    } catch (\Exception $e) {
        logger()->error('Checkout success error', ['error' => $e->getMessage()]);
        return redirect('/pricing')->with('error', 'Failed to verify checkout');
    }
})->name('checkout.success');

// Checkout cancel
Route::get('/checkout/cancel', function () {
    return view('front-end.checkout-cancel');
})->name('checkout.cancel');

/*
|--------------------------------------------------------------------------
| Billing Portal
|--------------------------------------------------------------------------
*/

// Redirect to Stripe Billing Portal
Route::get('/billing', function (Request $request) {
    $tenant = app(\App\Services\TenantService::class)->getTenant();

    if (!$tenant || !$tenant->hasStripeId()) {
        return redirect('/pricing')->with('error', 'No billing account found');
    }

    $stripeService = app(StripeService::class);
    $session = $stripeService->createBillingPortalSession($tenant, url('/admin'));

    return redirect($session->url);
})->middleware(['jwt.admin.verify', 'identify.tenant'])->name('billing');

/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
*/
// TODO: Create SubscribeController if newsletter feature is needed
// Route::get('/newsletter-subscribe/{company}', 'App\Http\Controllers\Web\SubscribeController@index')->name('newsletter-subscribe');

/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

Route::get('/reset-password', function () {
    return view('admin');
})->name('password.reset');

/*
|--------------------------------------------------------------------------
| Stripe Webhooks
|--------------------------------------------------------------------------
|
| Stripe webhook endpoint with custom idempotency and audit logging.
| Uses CSRF exemption as Stripe cannot send CSRF tokens.
|
*/
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
