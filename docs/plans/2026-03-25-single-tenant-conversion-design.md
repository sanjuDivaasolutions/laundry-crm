# Single-Tenant Conversion Design

**Date:** 2026-03-25
**Status:** Approved
**Approach:** Config Toggle (Approach A)

## Goal

Convert the Laundry CRM from multi-tenant SaaS to single-tenant mode while keeping all SaaS code dormant and reversible via a single config flag.

## Strategy

- Hardcode `tenant_id = 1` throughout
- Disable SaaS behavior (quotas, subscriptions, subdomain routing) via config toggle
- Keep all billing/Stripe/multi-tenant code intact but dormant
- Zero database schema changes
- Re-enable SaaS later by setting `TENANCY_SINGLE_TENANT=false`

## SaaS Footprint (What Becomes Dormant)

| Area | Files | Lines |
|------|-------|-------|
| Multi-tenancy core (trait, scopes, middleware) | 6 | ~1,100 |
| Billing/Stripe (services, controllers, jobs) | 18 | ~3,300 |
| Tenant services (TenantService, QuotaService, CacheService) | 3 | ~611 |
| Migrations (tenant_id columns, billing tables) | 23 | ~1,300 |
| Frontend (tenant module, billing UI, header injection) | ~20 | ~2,000 |
| Config + routes | 3 | ~800 |
| Tests (tenant isolation, billing, quota) | 8+ | ~1,500 |
| **Total** | **~80+** | **~10,600+** |

## Changes Required

### 1. Config Toggle

Add to `config/tenancy.php`:

```php
'single_tenant_mode' => env('TENANCY_SINGLE_TENANT', true),
'default_tenant_id' => env('TENANCY_DEFAULT_TENANT_ID', 1),
```

### 2. Backend Changes (5 files)

**`app/Traits/BelongsToTenant.php`**
- In `bootBelongsToTenant()`, when single-tenant mode: global scope filters by `default_tenant_id`, creating hook sets `tenant_id = default_tenant_id`, skip fail-safe logic.

**`app/Http/Middleware/IdentifyTenant.php`**
- At top of `handle()`, when single-tenant mode: load tenant ID 1, set on TenantService, skip resolution chain.

**`app/Http/Middleware/EnforceTenantQuota.php`**
- At top of `handle()`, when single-tenant mode: `return $next($request)` immediately.

**`app/Services/TenantService.php`**
- In `getId()`, when single-tenant mode and no tenant set: return `default_tenant_id`.

**`routes/tenant_api.php`**
- Wrap SaaS-specific routes in `if (!config('tenancy.single_tenant_mode'))` guard.

### 3. Frontend Changes

**None required.** `ApiService.ts` already defaults to `"1"` for tenant ID. Tenant module and TenantSignUp become unreachable via disabled backend routes.

### 4. Database

**No migrations.** `tenant_id` column stays in all 15 tables with value `1`. Seeder must ensure tenant with `id=1` exists.

### 5. Testing

- Tenant isolation tests pass as-is (scope still works, resolves to 1)
- Billing/quota tests add `config(['tenancy.single_tenant_mode' => false])` in setUp to exercise dormant code
- All business tests (orders, POS, payments) pass without changes

## Change Summary

| File | Change | Lines |
|------|--------|-------|
| `config/tenancy.php` | Add `single_tenant_mode` and `default_tenant_id` | +3 |
| `app/Traits/BelongsToTenant.php` | Early return in boot when single-tenant | +8 |
| `app/Http/Middleware/IdentifyTenant.php` | Short-circuit to tenant ID 1 | +5 |
| `app/Http/Middleware/EnforceTenantQuota.php` | Early return | +4 |
| `app/Services/TenantService.php` | Fallback getId() | +3 |
| `routes/tenant_api.php` | Wrap SaaS routes in config guard | +4 |
| `.env` | Add `TENANCY_SINGLE_TENANT=true` | +1 |
| Billing/quota tests | Config override in setUp | ~+6 |
| **Total** | | **~34 lines** |

## What Stays Untouched

- All 41 models, 44 controllers, 122 form requests
- All billing/Stripe code (dormant but intact)
- All Vue components
- All 73 migrations
- Database schema
- `bootstrap/app.php`
- `composer.json` / `package.json`
