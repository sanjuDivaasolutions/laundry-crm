<?php

/*
 *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *
 *  Multi-Tenant API Routes
 *  All routes related to tenant management, registration, and lifecycle.
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Stripe Webhooks (No Auth, No CSRF)
|--------------------------------------------------------------------------
*/
Route::post('stripe/webhook', [\App\Http\Controllers\Api\StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

/*
|--------------------------------------------------------------------------
| Public Tenant Registration Routes (No Auth)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1/register', 'as' => 'api.register.'], function () {
    // Check subdomain availability
    Route::get('check-subdomain', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'checkSubdomain'])
        ->name('check-subdomain');

    // Suggest subdomain from company name
    Route::get('suggest-subdomain', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'suggestSubdomain'])
        ->name('suggest-subdomain');

    // Get available timezones
    Route::get('timezones', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'getTimezones'])
        ->name('timezones');

    // Get supported currencies
    Route::get('currencies', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'getCurrencies'])
        ->name('currencies');

    // Register new tenant
    Route::post('/', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'register'])
        ->middleware('throttle:5,1') // 5 attempts per minute
        ->name('store');

    // Email verification
    Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'verifyEmail'])
        ->name('verify-email');

    // Resend verification email
    Route::post('resend-verification', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'resendVerification'])
        ->middleware('throttle:3,1') // 3 attempts per minute
        ->name('resend-verification');
});

/*
|--------------------------------------------------------------------------
| Authenticated Tenant Routes (Requires Auth + Tenant)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1', 'as' => 'api.', 'middleware' => ['jwt.admin.verify', 'identify.tenant']], function () {
    // Subscription management
    Route::get('subscription', [\App\Http\Controllers\Api\SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('subscription/change-plan', [\App\Http\Controllers\Api\SubscriptionController::class, 'changePlan'])->name('subscription.change-plan');
    Route::post('subscription/cancel', [\App\Http\Controllers\Api\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('subscription/resume', [\App\Http\Controllers\Api\SubscriptionController::class, 'resume'])->name('subscription.resume');

    // Billing management
    Route::get('billing', [\App\Http\Controllers\Api\BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/portal', [\App\Http\Controllers\Api\BillingController::class, 'portal'])->name('billing.portal');
    Route::get('billing/invoices', [\App\Http\Controllers\Api\BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('billing/upcoming', [\App\Http\Controllers\Api\BillingController::class, 'upcomingInvoice'])->name('billing.upcoming');
    Route::post('billing/payment-method', [\App\Http\Controllers\Api\BillingController::class, 'updatePaymentMethod'])->name('billing.payment-method');
    Route::post('billing/setup-intent', [\App\Http\Controllers\Api\BillingController::class, 'createSetupIntent'])->name('billing.setup-intent');

    // Subscription actions (used by quota middleware responses)
    Route::get('billing/subscribe', [\App\Http\Controllers\Api\BillingController::class, 'showSubscribePage'])->name('billing.subscribe');
    Route::get('billing/upgrade', [\App\Http\Controllers\Api\BillingController::class, 'showUpgradePage'])->name('billing.upgrade');

    // Checkout
    Route::post('checkout', [\App\Http\Controllers\Api\CheckoutController::class, 'createSession'])->name('checkout.create');

    // Announcements for current tenant
    Route::get('announcements', [\App\Http\Controllers\Api\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements/{announcement}/dismiss', [\App\Http\Controllers\Api\AnnouncementController::class, 'dismiss'])->name('announcements.dismiss');

    // Tenant settings (for current tenant)
    Route::get('tenant/settings', [\App\Http\Controllers\Api\TenantSettingsController::class, 'index'])->name('tenant.settings.index');
    Route::put('tenant/settings', [\App\Http\Controllers\Api\TenantSettingsController::class, 'update'])->name('tenant.settings.update');
    Route::get('tenant/profile', [\App\Http\Controllers\Api\TenantSettingsController::class, 'profile'])->name('tenant.profile');
    Route::put('tenant/profile', [\App\Http\Controllers\Api\TenantSettingsController::class, 'updateProfile'])->name('tenant.profile.update');
    Route::post('tenant/logo', [\App\Http\Controllers\Api\TenantSettingsController::class, 'uploadLogo'])->name('tenant.logo.upload');
});

/*
|--------------------------------------------------------------------------
| Super Admin Tenant Management Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1/admin', 'as' => 'api.admin.', 'middleware' => ['jwt.admin.verify']], function () {
    // Tenant statistics/dashboard
    Route::get('tenants/statistics', [\App\Http\Controllers\Admin\TenantApiController::class, 'statistics'])->name('tenants.statistics');

    // Tenant CRUD
    Route::get('tenants', [\App\Http\Controllers\Admin\TenantApiController::class, 'index'])->name('tenants.index');
    Route::get('tenants/{tenant}', [\App\Http\Controllers\Admin\TenantApiController::class, 'show'])->name('tenants.show');
    Route::put('tenants/{tenant}', [\App\Http\Controllers\Admin\TenantApiController::class, 'update'])->name('tenants.update');

    // Tenant actions
    Route::post('tenants/{tenant}/suspend', [\App\Http\Controllers\Admin\TenantApiController::class, 'suspend'])->name('tenants.suspend');
    Route::post('tenants/{tenant}/reactivate', [\App\Http\Controllers\Admin\TenantApiController::class, 'reactivate'])->name('tenants.reactivate');
    Route::post('tenants/{tenant}/extend-trial', [\App\Http\Controllers\Admin\TenantApiController::class, 'extendTrial'])->name('tenants.extend-trial');
    Route::post('tenants/{tenant}/impersonate', [\App\Http\Controllers\Admin\TenantApiController::class, 'impersonate'])->name('tenants.impersonate');

    // Announcement management (super admin)
    Route::get('announcements', [\App\Http\Controllers\Admin\AnnouncementApiController::class, 'index'])->name('announcements.index');
    Route::post('announcements', [\App\Http\Controllers\Admin\AnnouncementApiController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementApiController::class, 'show'])->name('announcements.show');
    Route::put('announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementApiController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementApiController::class, 'destroy'])->name('announcements.destroy');
});

/*
|--------------------------------------------------------------------------
| Public SaaS Routes (No Auth - Plans/Pricing)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {
    // Plans - public listing for pricing page
    Route::get('plans', [\App\Http\Controllers\Api\PlanController::class, 'index'])->name('plans.index');
    Route::get('plans/compare', [\App\Http\Controllers\Api\PlanController::class, 'compare'])->name('plans.compare');
    Route::get('plans/{code}', [\App\Http\Controllers\Api\PlanController::class, 'show'])->name('plans.show');

    // Checkout callbacks (no auth - called from Stripe redirect)
    Route::get('checkout/success', [\App\Http\Controllers\Api\CheckoutController::class, 'handleSuccess'])->name('checkout.success');
    Route::get('checkout/cancel', [\App\Http\Controllers\Api\CheckoutController::class, 'handleCancel'])->name('checkout.cancel');
});
