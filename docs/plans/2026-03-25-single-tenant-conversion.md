# Single-Tenant Conversion Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Convert Laundry CRM from multi-tenant SaaS to single-tenant mode via a config toggle, keeping all SaaS code dormant and reversible.

**Architecture:** Add `single_tenant_mode` flag to `config/tenancy.php`. When true, all tenant resolution short-circuits to tenant ID 1, quota checks are skipped, and SaaS routes are disabled. All existing code stays intact.

**Tech Stack:** Laravel 11, PHP 8.2, Vue 3, Pest testing

---

### Task 1: Add Config Toggle

**Files:**
- Modify: `config/tenancy.php:1-5`
- Modify: `.env.example:77`

**Step 1: Add single-tenant config keys to tenancy.php**

Add these two keys at the very top of the return array (after line 3 `return [`):

```php
    /*
    |--------------------------------------------------------------------------
    | Single Tenant Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the application runs in single-tenant mode:
    | - Tenant resolution always uses default_tenant_id
    | - Quota enforcement is skipped
    | - SaaS routes (registration, billing, subscriptions) are disabled
    | - Set to false to re-enable multi-tenant SaaS mode
    |
    */
    'single_tenant_mode' => env('TENANCY_SINGLE_TENANT', true),
    'default_tenant_id' => env('TENANCY_DEFAULT_TENANT_ID', 1),
```

**Step 2: Add env vars to .env.example**

Append to end of `.env.example`:

```
# Tenancy
TENANCY_SINGLE_TENANT=true
TENANCY_DEFAULT_TENANT_ID=1
```

**Step 3: Verify config loads**

Run: `php artisan config:clear && php artisan tinker --execute="echo config('tenancy.single_tenant_mode') ? 'single' : 'multi';"`
Expected: `single`

**Step 4: Commit**

```bash
git add config/tenancy.php .env.example
git commit -m "feat: add single_tenant_mode config toggle to tenancy.php"
```

---

### Task 2: Modify TenantService Fallback

**Files:**
- Modify: `app/Services/TenantService.php:36-40`
- Test: `tests/Unit/Services/TenantServiceTest.php` (create)

**Step 1: Write the failing test**

Create `tests/Unit/Services/TenantServiceTest.php`:

```php
<?php

use App\Services\TenantService;

it('returns default tenant id in single tenant mode when no tenant set', function () {
    config(['tenancy.single_tenant_mode' => true]);
    config(['tenancy.default_tenant_id' => 1]);

    $service = new TenantService();
    // No tenant set via setTenant()

    expect($service->getId())->toBe(1);
});

it('returns null when no tenant set in multi-tenant mode', function () {
    config(['tenancy.single_tenant_mode' => false]);

    $service = new TenantService();

    expect($service->getId())->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter="returns default tenant id in single tenant mode"`
Expected: FAIL (getId returns null, not 1)

**Step 3: Modify TenantService.php**

Replace the `getId()` method in `app/Services/TenantService.php`:

```php
    /**
     * Get the current tenant ID.
     */
    public function getId(): ?int
    {
        if ($this->tenant) {
            return $this->tenant->id;
        }

        // Single-tenant fallback
        if (config('tenancy.single_tenant_mode')) {
            return (int) config('tenancy.default_tenant_id', 1);
        }

        return null;
    }
```

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter="TenantServiceTest"`
Expected: PASS (both tests)

**Step 5: Commit**

```bash
git add app/Services/TenantService.php tests/Unit/Services/TenantServiceTest.php
git commit -m "feat: add single-tenant fallback to TenantService::getId()"
```

---

### Task 3: Short-Circuit IdentifyTenant Middleware

**Files:**
- Modify: `app/Http/Middleware/IdentifyTenant.php:49-67`

**Step 1: Add single-tenant short-circuit at top of handle()**

Replace the `handle()` method body (lines 49-67) with:

```php
    public function handle(Request $request, Closure $next): Response
    {
        // Single-tenant mode: skip resolution chain, use default tenant
        if (config('tenancy.single_tenant_mode')) {
            $defaultId = (int) config('tenancy.default_tenant_id', 1);
            $tenant = Tenant::find($defaultId);

            if ($tenant) {
                $this->tenantService->setTenant($tenant);
                $request->attributes->set('tenant_resolution_method', 'single_tenant_mode');
            }

            return $next($request);
        }

        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            if (! $tenant->active) {
                return $this->tenantInactiveResponse($tenant);
            }

            $this->tenantService->setTenant($tenant);

            logger()->debug('Tenant context established', [
                'tenant_id' => $tenant->id,
                'resolution_method' => $request->attributes->get('tenant_resolution_method', 'unknown'),
            ]);
        }

        return $next($request);
    }
```

**Step 2: Run existing tenant tests**

Run: `php artisan test --compact --filter="TenantIsolation"`
Expected: PASS (tenant scoping still works, resolves to ID 1)

**Step 3: Commit**

```bash
git add app/Http/Middleware/IdentifyTenant.php
git commit -m "feat: short-circuit IdentifyTenant in single-tenant mode"
```

---

### Task 4: Skip Quota Enforcement

**Files:**
- Modify: `app/Http/Middleware/EnforceTenantQuota.php:43-45`

**Step 1: Add early return at top of handle()**

Add these lines immediately inside the `handle()` method, before the existing `$tenant = ...` line:

```php
        // Single-tenant mode: skip all quota enforcement
        if (config('tenancy.single_tenant_mode')) {
            return $next($request);
        }
```

The method should now start with:

```php
    public function handle(Request $request, Closure $next, ?string $resource = null, int $amount = 1): Response
    {
        // Single-tenant mode: skip all quota enforcement
        if (config('tenancy.single_tenant_mode')) {
            return $next($request);
        }

        $tenant = $this->tenantService->getTenant();
        // ... rest unchanged
```

**Step 2: Verify existing quota tests still pass with override**

Run: `php artisan test --compact --filter="QuotaAtomicOperations"`
Expected: PASS (these tests should set `single_tenant_mode => false` if they rely on quota logic; if they fail, see Task 6)

**Step 3: Commit**

```bash
git add app/Http/Middleware/EnforceTenantQuota.php
git commit -m "feat: skip quota enforcement in single-tenant mode"
```

---

### Task 5: Simplify BelongsToTenant in Single-Tenant Mode

**Files:**
- Modify: `app/Traits/BelongsToTenant.php:40-89`

**Step 1: Modify the global scope in bootBelongsToTenant()**

Replace the `bootBelongsToTenant()` method (lines 40-90) with:

```php
    public static function bootBelongsToTenant(): void
    {
        // Single-tenant mode: simplified scoping
        if (config('tenancy.single_tenant_mode')) {
            $defaultTenantId = (int) config('tenancy.default_tenant_id', 1);

            static::addGlobalScope('tenant', function (Builder $builder) use ($defaultTenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $defaultTenantId);
            });

            static::creating(function (Model $model) use ($defaultTenantId) {
                if (!$model->getAttribute('tenant_id')) {
                    $model->setAttribute('tenant_id', $defaultTenantId);
                }
            });

            return;
        }

        // Multi-tenant mode: full resolution chain (existing code)
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Check if scope bypass is explicitly allowed
            if (static::$bypassTenantScope) {
                static::$bypassTenantScope = false; // Reset for next query
                return;
            }

            $tenantService = app(TenantService::class);
            $tenantId = $tenantService->getId();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
                return;
            }

            // FAIL-SAFE: No tenant context
            if (static::shouldEnforceStrictTenantScope()) {
                static::handleMissingTenantContext($builder);
            }
        });

        // Auto-assign tenant_id on creation
        static::creating(function (Model $model) {
            $tenantService = app(TenantService::class);

            if ($model->getAttribute('tenant_id')) {
                return;
            }

            $tenantId = $tenantService->getId();

            if ($tenantId) {
                $model->setAttribute('tenant_id', $tenantId);
                return;
            }

            if (static::shouldEnforceStrictTenantScope()) {
                logger()->error('Attempted to create tenant-scoped model without tenant context', [
                    'model' => get_class($model),
                    'attributes' => $model->getAttributes(),
                ]);

                throw TenantResolutionException::missingContext();
            }
        });
    }
```

**Step 2: Run all existing tests**

Run: `php artisan test --compact`
Expected: All business tests PASS (orders, POS, customers all scoped to tenant 1)

**Step 3: Commit**

```bash
git add app/Traits/BelongsToTenant.php
git commit -m "feat: simplify BelongsToTenant scoping in single-tenant mode"
```

---

### Task 6: Disable SaaS Routes

**Files:**
- Modify: `routes/tenant_api.php`

**Step 1: Wrap SaaS-only route groups in config guard**

Replace the entire file content with:

```php
<?php

/*
 *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *
 *  Multi-Tenant API Routes
 *  All routes related to tenant management, registration, and lifecycle.
 *  Routes wrapped in single_tenant_mode check are disabled in single-tenant mode.
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes disabled in single-tenant mode
|--------------------------------------------------------------------------
*/
if (!config('tenancy.single_tenant_mode')) {

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhooks (No Auth, No CSRF)
    |--------------------------------------------------------------------------
    */
    Route::post('stripe/webhook', [\App\Http\Controllers\Webhooks\StripeWebhookController::class, 'handleWebhook'])
        ->name('cashier.webhook');

    /*
    |--------------------------------------------------------------------------
    | Public Tenant Registration Routes (No Auth)
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'v1/register', 'as' => 'api.register.'], function () {
        Route::get('check-subdomain', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'checkSubdomain'])
            ->name('check-subdomain');
        Route::get('suggest-subdomain', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'suggestSubdomain'])
            ->name('suggest-subdomain');
        Route::get('timezones', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'getTimezones'])
            ->name('timezones');
        Route::get('currencies', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'getCurrencies'])
            ->name('currencies');
        Route::post('/', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'register'])
            ->middleware('throttle:5,1')
            ->name('store');
        Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'verifyEmail'])
            ->name('verify-email');
        Route::post('resend-verification', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'resendVerification'])
            ->middleware('throttle:3,1')
            ->name('resend-verification');
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated Tenant Routes - SaaS Only (Billing/Subscriptions)
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
        Route::get('billing/subscribe', [\App\Http\Controllers\Api\BillingController::class, 'showSubscribePage'])->name('billing.subscribe');
        Route::get('billing/upgrade', [\App\Http\Controllers\Api\BillingController::class, 'showUpgradePage'])->name('billing.upgrade');

        // Checkout
        Route::post('checkout', [\App\Http\Controllers\Api\CheckoutController::class, 'createSession'])->name('checkout.create');
    });

    /*
    |--------------------------------------------------------------------------
    | Super Admin Tenant Management Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'v1/admin', 'as' => 'api.admin.', 'middleware' => ['jwt.admin.verify']], function () {
        Route::get('tenants/statistics', [\App\Http\Controllers\Admin\TenantApiController::class, 'statistics'])->name('tenants.statistics');
        Route::get('tenants', [\App\Http\Controllers\Admin\TenantApiController::class, 'index'])->name('tenants.index');
        Route::get('tenants/{tenant}', [\App\Http\Controllers\Admin\TenantApiController::class, 'show'])->name('tenants.show');
        Route::put('tenants/{tenant}', [\App\Http\Controllers\Admin\TenantApiController::class, 'update'])->name('tenants.update');
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
        Route::get('plans', [\App\Http\Controllers\Api\PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/compare', [\App\Http\Controllers\Api\PlanController::class, 'compare'])->name('plans.compare');
        Route::get('plans/{code}', [\App\Http\Controllers\Api\PlanController::class, 'show'])->name('plans.show');
        Route::get('checkout/success', [\App\Http\Controllers\Api\CheckoutController::class, 'handleSuccess'])->name('checkout.success');
        Route::get('checkout/cancel', [\App\Http\Controllers\Api\CheckoutController::class, 'handleCancel'])->name('checkout.cancel');
    });

} // end single_tenant_mode check

/*
|--------------------------------------------------------------------------
| Routes active in ALL modes (single-tenant AND multi-tenant)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1', 'as' => 'api.', 'middleware' => ['jwt.admin.verify', 'identify.tenant']], function () {
    // Announcements for current tenant (useful in both modes)
    Route::get('announcements', [\App\Http\Controllers\Api\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements/{announcement}/dismiss', [\App\Http\Controllers\Api\AnnouncementController::class, 'dismiss'])->name('announcements.dismiss');

    // Tenant settings (useful in both modes for branding/config)
    Route::get('tenant/settings', [\App\Http\Controllers\Api\TenantSettingsController::class, 'index'])->name('tenant.settings.index');
    Route::put('tenant/settings', [\App\Http\Controllers\Api\TenantSettingsController::class, 'update'])->name('tenant.settings.update');
    Route::get('tenant/profile', [\App\Http\Controllers\Api\TenantSettingsController::class, 'profile'])->name('tenant.profile');
    Route::put('tenant/profile', [\App\Http\Controllers\Api\TenantSettingsController::class, 'updateProfile'])->name('tenant.profile.update');
    Route::post('tenant/logo', [\App\Http\Controllers\Api\TenantSettingsController::class, 'uploadLogo'])->name('tenant.logo.upload');
});
```

**Step 2: Verify routes are disabled**

Run: `php artisan route:list --path=subscription 2>/dev/null | head -5`
Expected: No subscription routes listed (they are inside the disabled block)

Run: `php artisan route:list --path=announcements 2>/dev/null | head -5`
Expected: Announcement routes still listed (they are outside the guard)

**Step 3: Commit**

```bash
git add routes/tenant_api.php
git commit -m "feat: disable SaaS routes in single-tenant mode"
```

---

### Task 7: Update SaaS Tests with Config Override

**Files:**
- Modify: `tests/Feature/SaaSBillingTest.php`
- Modify: `tests/Feature/Billing/WebhookIdempotencyTest.php`
- Modify: `tests/Feature/Quota/QuotaAtomicOperationsTest.php`

**Step 1: Add config override to SaaSBillingTest.php**

Add this at the top of the test file, after any existing `beforeEach` or `setUp`:

```php
beforeEach(function () {
    config(['tenancy.single_tenant_mode' => false]);
});
```

If a `beforeEach` already exists, add the config line inside it.

**Step 2: Add config override to WebhookIdempotencyTest.php**

Same pattern:

```php
beforeEach(function () {
    config(['tenancy.single_tenant_mode' => false]);
});
```

**Step 3: Add config override to QuotaAtomicOperationsTest.php**

Same pattern:

```php
beforeEach(function () {
    config(['tenancy.single_tenant_mode' => false]);
});
```

**Step 4: Run all SaaS-related tests**

Run: `php artisan test --compact --filter="SaaSBilling|WebhookIdempotency|QuotaAtomicOperations"`
Expected: PASS

**Step 5: Run full test suite**

Run: `php artisan test --compact`
Expected: All tests PASS

**Step 6: Commit**

```bash
git add tests/Feature/SaaSBillingTest.php tests/Feature/Billing/WebhookIdempotencyTest.php tests/Feature/Quota/QuotaAtomicOperationsTest.php
git commit -m "test: add single_tenant_mode=false override to SaaS tests"
```

---

### Task 8: Update .env and Verify End-to-End

**Files:**
- Modify: `.env` (if it exists locally)

**Step 1: Add env vars to local .env**

Append to `.env`:

```
# Tenancy
TENANCY_SINGLE_TENANT=true
TENANCY_DEFAULT_TENANT_ID=1
```

**Step 2: Clear all caches**

Run: `php artisan optimize:clear`
Expected: Compiled views/config/routes cleared

**Step 3: Run full test suite one final time**

Run: `php artisan test --compact`
Expected: All tests PASS

**Step 4: Commit any remaining changes**

```bash
git add -A
git commit -m "feat: complete single-tenant conversion with config toggle

Single-tenant mode enabled via TENANCY_SINGLE_TENANT=true.
All SaaS code (billing, subscriptions, quotas, tenant registration)
remains dormant but intact. Set TENANCY_SINGLE_TENANT=false to
re-enable multi-tenant SaaS mode."
```

---

## Post-Conversion: Project Audit

After the single-tenant conversion is complete, proceed with the comprehensive project audit. This is a **findings report only** (no code fixes) using risk-based sampling.

### Audit Task 1: Security & Authentication
- Review JWT middleware for token validation, expiry, refresh
- Check OWASP top 10 across sampled controllers (OrderApiController, CustomerApiController, LoginController)
- Verify input validation in sampled Form Requests
- Check for SQL injection in HasAdvancedFilter trait
- Check XSS protection in API responses and Vue components
- Check CORS configuration

### Audit Task 2: Data Integrity
- Review migration constraints (foreign keys, unique indexes, NOT NULL)
- Check for race conditions in POS payment processing
- Verify soft delete consistency across related models
- Check order number/customer code generation for uniqueness guarantees

### Audit Task 3: Performance
- Grep for N+1 patterns in controllers (missing eager loading)
- Review database indexes vs query patterns
- Check frontend bundle size and unused Metronic components
- Review caching strategy (TenantCacheService usage)

### Audit Task 4: Code Quality
- Check for dead code, unused imports, duplicated logic
- Review TypeScript coverage in frontend
- Check for inconsistent patterns across modules
- Review error handling consistency

### Audit Task 5: Test Coverage Gaps
- Map which controllers/services have no tests
- Identify untested edge cases in existing tests
- Check for missing validation tests on Form Requests

### Audit Task 6: Production Readiness
- Review queue configuration (sync vs async)
- Check error reporting and monitoring setup
- Verify logging configuration
- Review deployment configuration
- Check for hardcoded credentials or secrets
