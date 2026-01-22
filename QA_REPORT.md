# SaaS Basecode - QA Analysis Report

> **QA Engineer:** Claude AI
> **Date:** 2026-01-22
> **Version:** 1.0
> **Status:** Critical Issues Identified

---

## Executive Summary

This QA report provides a comprehensive analysis of the SaaS basecode project. The analysis covers authentication, multi-tenancy, Stripe payment integration, subscription management, and overall architecture. **Several critical gaps have been identified that prevent the system from functioning as a complete SaaS platform.**

### Overall Assessment: **NOT PRODUCTION READY**

| Category | Status | Severity |
|----------|--------|----------|
| Authentication | Partial | Medium |
| Multi-tenancy | Solid Foundation | Low |
| Stripe Integration | Incomplete | Critical |
| Subscription Flow | Missing | Critical |
| User Signup | Missing | Critical |
| Plan Management | Missing | Critical |
| Billing Portal | Missing | High |

---

## 1. Authentication Flow Analysis

### Current Implementation

**Flow:**
```
POST /api/v1/login
  ├─ Validate email + password
  ├─ Check user.active = 1
  ├─ Generate JWT token (1 hour TTL)
  └─ Return user data + abilities + token
```

**Files Involved:**
- `app/Http/Controllers/Admin/Auth/LoginController.php`
- `app/Services/AuthService.php`
- `config/jwt.php`

### Issues Found

| Issue ID | Description | Severity | Status |
|----------|-------------|----------|--------|
| AUTH-001 | No tenant validation during login | High | Open |
| AUTH-002 | No signup/registration endpoint | Critical | Open |
| AUTH-003 | JWT custom claims don't include tenant_id | Medium | Open |
| AUTH-004 | No email verification flow | Medium | Open |
| AUTH-005 | Password reset works but no tenant context | Low | Open |

#### AUTH-001: No Tenant Validation During Login

**Problem:** The login controller validates credentials but doesn't verify if the user's tenant is active.

```php
// Current code in LoginController.php
$credentials = $request->only('email', 'password');
$credentials['active'] = 1;  // Only checks user active, not tenant active

$token = auth('admin')->attempt($credentials);
```

**Risk:** Users from inactive/suspended tenants can still log in.

**Recommendation:**
```php
$token = auth('admin')->attempt($credentials);
if ($token) {
    $user = auth('admin')->user();
    if (!$user->tenant || !$user->tenant->active) {
        auth('admin')->logout();
        return errorResponse('Your account has been suspended', 403);
    }
}
```

#### AUTH-002: No Signup/Registration Endpoint

**Problem:** There is no `/signup` or `/register` endpoint for new tenant onboarding.

**Impact:** Cannot onboard new customers through the application.

**Required Implementation:**
```
POST /api/v1/signup
├─ Create new Tenant
├─ Create admin User for tenant
├─ Set up default quotas/features
├─ Optional: Start trial period
├─ Optional: Redirect to Stripe checkout
└─ Return JWT token + user data
```

#### AUTH-003: JWT Custom Claims Missing Tenant ID

**Problem:** `getJWTCustomClaims()` returns empty array.

```php
// Current in User.php
public function getJWTCustomClaims()
{
    return [];
}
```

**Recommendation:**
```php
public function getJWTCustomClaims()
{
    return [
        'tenant_id' => $this->tenant_id,
        'roles' => $this->roles->pluck('id')->toArray(),
    ];
}
```

---

## 2. Multi-Tenancy Analysis

### Current Implementation - SOLID FOUNDATION

**Architecture:**
```
Tenant (parent)
├── Users (tenant_id FK)
├── Companies (tenant_id FK)
├── Roles (tenant_id FK)
├── TenantFeatures
├── TenantQuotas
├── TenantUsage
└── TenantSubscriptions
```

**Key Components:**
- `app/Http/Middleware/IdentifyTenant.php` - Tenant resolution
- `app/Traits/BelongsToTenant.php` - Global scope
- `app/Services/TenantService.php` - Context management

### Strengths

1. **Fail-safe tenant scoping** - Returns empty results if no tenant context
2. **Multiple resolution strategies** - User, domain, signature-based
3. **Bypass logging** - All scope bypasses are logged
4. **Atomic quota operations** - Race condition prevention

### Issues Found

| Issue ID | Description | Severity | Status |
|----------|-------------|----------|--------|
| TENANT-001 | Company model not linked to User selection | Medium | Open |
| TENANT-002 | No tenant provisioning workflow | Critical | Open |
| TENANT-003 | No tenant admin panel for management | High | Open |
| TENANT-004 | Tenant deletion/data export not implemented | Medium | Open |

#### TENANT-001: Company Selection Not Validated

**Problem:** User can have `company_id` in settings, but no validation ensures they can access that company.

```php
// In AuthService.php
public static function getCompanyId()
{
    $user = self::getUser();
    $settings = $user->settings;
    return $settings['company_id'] ?? null;  // No validation!
}
```

---

## 3. Stripe Integration Analysis

### Current Implementation - PARTIALLY IMPLEMENTED

**Components Present:**
- `app/Models/Tenant.php` - Has `Billable` trait
- `app/Services/Billing/SaaSSubscriptionService.php` - Basic subscribe/cancel
- `app/Http/Controllers/Webhooks/StripeWebhookController.php` - Webhook handling
- `database/migrations/2026_01_13_000000_create_tenant_billing_tables.php` - Schema

**Components Missing:**
- StripeService.php (referenced in routes but doesn't exist)
- Contract model (referenced in routes but doesn't exist)
- Plan/Product configuration
- Checkout flow
- Customer portal

### Critical Issues

| Issue ID | Description | Severity | Status |
|----------|-------------|----------|--------|
| STRIPE-001 | StripeService.php does not exist | Critical | Open |
| STRIPE-002 | Contract model does not exist | Critical | Open |
| STRIPE-003 | No plan/product configuration | Critical | Open |
| STRIPE-004 | No checkout session creation | Critical | Open |
| STRIPE-005 | No payment method management | High | Open |
| STRIPE-006 | No customer billing portal | High | Open |
| STRIPE-007 | Dunning flow marked as TODO | High | Open |

#### STRIPE-001: Missing StripeService.php

**Problem:** `routes/web.php` references `StripeService` but it doesn't exist:

```php
// routes/web.php line 46-50
Route::get('/subscription-checkout/{contract}', function (Contract $contract) {
    if (! StripeService::isValidContract($contract)) {  // ERROR: Class not found
        return redirect()->route('contract-invalid');
    }
    return StripeService::createContractSubscription($contract);  // ERROR
});
```

**Impact:** Subscription checkout route will throw a fatal error.

#### STRIPE-002: Missing Contract Model

**Problem:** `Contract` model is imported and used but doesn't exist.

```php
use App\Models\Contract;  // This class does not exist
```

**Impact:** Route model binding will fail.

#### STRIPE-003: No Plan Configuration

**Problem:** No plans/products defined anywhere in the system.

**Required:**
```php
// config/plans.php or database table
return [
    'starter' => [
        'name' => 'Starter',
        'stripe_price_id' => 'price_xxx',
        'features' => ['api_access', 'basic_support'],
        'quotas' => [
            'max_users' => 5,
            'storage_gb' => 10,
            'api_calls' => 10000,
        ],
    ],
    'professional' => [
        'name' => 'Professional',
        'stripe_price_id' => 'price_yyy',
        // ...
    ],
];
```

#### STRIPE-007: Incomplete Dunning Flow

**Problem:** Payment failure handler has TODO:

```php
// StripeWebhookController.php line 419
protected function handleInvoicePaymentFailed($invoice, StripeWebhookLog $log): array
{
    // ...
    logger()->warning('Tenant payment failed', [...]);

    // TODO: Trigger dunning email/notification  <-- NOT IMPLEMENTED

    return [...];
}
```

---

## 4. Subscription Flow Analysis

### Expected Flow (NOT IMPLEMENTED)

```
1. New User Signup
   └─ POST /api/v1/signup
      ├─ Create Tenant
      ├─ Create User
      └─ Return JWT + redirect to plan selection

2. Plan Selection
   └─ GET /api/v1/plans
      └─ Return available plans with features/prices

3. Checkout
   └─ POST /api/v1/checkout
      ├─ Create Stripe Checkout Session
      └─ Redirect to Stripe hosted checkout

4. Webhook Processing
   └─ POST /stripe/webhook
      ├─ checkout.session.completed
      ├─ customer.subscription.created
      └─ Activate tenant features/quotas

5. Post-Checkout
   └─ GET /checkout/success
      └─ Confirm subscription, enable features
```

### Current State

| Step | Status | Notes |
|------|--------|-------|
| 1. Signup | Missing | No endpoint |
| 2. Plan Selection | Missing | No plans configured |
| 3. Checkout | Broken | References non-existent classes |
| 4. Webhook Processing | Partial | Works but limited |
| 5. Post-Checkout | Broken | Views exist but flow broken |

---

## 5. Missing API Endpoints

### Critical Missing Endpoints

```
# Tenant Onboarding
POST   /api/v1/signup                    # New tenant registration
POST   /api/v1/verify-email              # Email verification

# Subscription Management
GET    /api/v1/plans                     # List available plans
POST   /api/v1/checkout                  # Create checkout session
GET    /api/v1/subscription              # Current subscription status
POST   /api/v1/subscription/cancel       # Cancel subscription
POST   /api/v1/subscription/resume       # Resume subscription
POST   /api/v1/subscription/change-plan  # Upgrade/downgrade

# Billing
GET    /api/v1/invoices                  # List invoices
GET    /api/v1/invoices/{id}/download    # Download invoice PDF
GET    /api/v1/billing-portal            # Redirect to Stripe portal
POST   /api/v1/payment-methods           # Add payment method
DELETE /api/v1/payment-methods/{id}      # Remove payment method

# Tenant Management (Admin)
GET    /api/v1/tenant                    # Current tenant info
PUT    /api/v1/tenant                    # Update tenant settings
GET    /api/v1/tenant/usage              # Usage statistics
GET    /api/v1/tenant/quotas             # Quota status
```

---

## 6. Database Schema Gaps

### Missing Tables

| Table | Purpose | Priority |
|-------|---------|----------|
| `plans` | Store plan definitions | Critical |
| `plan_features` | Map features to plans | Critical |
| `plan_quotas` | Map quotas to plans | Critical |
| `email_verifications` | Email verification tokens | High |
| `audit_logs` | Comprehensive audit trail | Medium |
| `notifications` | User notifications | Medium |

### Recommended Plans Schema

```sql
CREATE TABLE plans (
    id BIGINT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(100),
    description TEXT,
    stripe_product_id VARCHAR(100),
    stripe_price_id VARCHAR(100),
    price_cents INT,
    billing_period ENUM('monthly', 'yearly'),
    trial_days INT DEFAULT 14,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE plan_features (
    id BIGINT PRIMARY KEY,
    plan_id BIGINT REFERENCES plans(id),
    feature_code VARCHAR(50),
    enabled BOOLEAN DEFAULT TRUE,
    UNIQUE(plan_id, feature_code)
);

CREATE TABLE plan_quotas (
    id BIGINT PRIMARY KEY,
    plan_id BIGINT REFERENCES plans(id),
    quota_code VARCHAR(50),
    limit_value INT,  -- -1 for unlimited
    period ENUM('lifetime', 'daily', 'monthly', 'yearly'),
    UNIQUE(plan_id, quota_code)
);
```

---

## 7. Test Scenarios

### Authentication Tests

| Test ID | Scenario | Expected Result | Automated |
|---------|----------|-----------------|-----------|
| AUTH-T01 | Valid login | JWT token returned | No |
| AUTH-T02 | Invalid password | 401 error | No |
| AUTH-T03 | Inactive user login | 401 error | No |
| AUTH-T04 | Inactive tenant user login | 403 error | No |
| AUTH-T05 | Token expiration | 401 after 1 hour | No |
| AUTH-T06 | Token refresh | New valid token | No |

### Multi-Tenancy Tests

| Test ID | Scenario | Expected Result | Automated |
|---------|----------|-----------------|-----------|
| MT-T01 | Query with tenant context | Only tenant data returned | Partial |
| MT-T02 | Query without context | Empty result (fail-safe) | Partial |
| MT-T03 | Cross-tenant data access | Blocked | Partial |
| MT-T04 | Tenant scope bypass | Logged | Partial |
| MT-T05 | Auto tenant_id on create | tenant_id set | Partial |

### Subscription Tests (CANNOT BE TESTED - NOT IMPLEMENTED)

| Test ID | Scenario | Expected Result | Blocker |
|---------|----------|-----------------|---------|
| SUB-T01 | New subscription checkout | Redirect to Stripe | StripeService missing |
| SUB-T02 | Webhook subscription.created | Quotas set up | Contract missing |
| SUB-T03 | Payment failure | Dunning triggered | TODO in code |
| SUB-T04 | Subscription cancellation | Access revoked | Not implemented |
| SUB-T05 | Plan upgrade | Features updated | Not implemented |

---

## 8. Security Concerns

### High Priority

| Issue | Description | Recommendation |
|-------|-------------|----------------|
| SEC-001 | No rate limiting on login | Add throttle middleware |
| SEC-002 | No brute force protection | Implement lockout after N attempts |
| SEC-003 | Sensitive routes exposed | Remove `/reinstall-permissions`, `/optimize` from production |
| SEC-004 | No CORS configuration visible | Verify CORS settings |
| SEC-005 | JWT secret in .env | Ensure proper secret rotation policy |

### Routes to Remove/Protect

```php
// These routes should NOT be in production:
Route::get('storage-link', ...);        // System command
Route::get('optimize', ...);            // System command
Route::get('reinstall-permissions', ...); // Destructive operation
```

---

## 9. Recommended Implementation Roadmap

### Phase 1: Critical Fixes (Week 1)

1. **Create StripeService.php**
   - Implement `isValidContract()`
   - Implement `createContractSubscription()`
   - Or: Remove broken routes

2. **Create Contract Model** (if using contract-based flow)
   - Or: Implement direct plan-based checkout

3. **Add Signup Endpoint**
   - Tenant creation
   - User creation
   - JWT generation

4. **Create Plans Configuration**
   - Database tables or config file
   - Feature/quota mapping

### Phase 2: Core Subscription Flow (Week 2)

1. **Checkout Flow**
   - Plan selection API
   - Stripe Checkout Session creation
   - Success/cancel handlers

2. **Webhook Handlers**
   - Complete checkout.session.completed
   - Implement dunning flow
   - Email notifications

3. **Tenant Provisioning**
   - Auto-setup quotas from plan
   - Auto-setup features from plan

### Phase 3: Billing Portal (Week 3)

1. **Invoice Management**
   - List invoices API
   - Download PDF

2. **Payment Methods**
   - Add/remove payment methods
   - Stripe Elements integration

3. **Subscription Management UI**
   - Current plan display
   - Upgrade/downgrade
   - Cancel/resume

### Phase 4: Polish (Week 4)

1. **Email Notifications**
   - Welcome email
   - Payment receipt
   - Payment failure warning
   - Subscription expiring

2. **Admin Features**
   - Tenant management
   - Manual subscription override
   - Usage analytics

---

## 10. Files to Create

### Critical (Before Testing)

```
app/Services/StripeService.php           # Or remove broken routes
app/Models/Contract.php                   # Or use different flow
app/Http/Controllers/Api/SignupController.php
app/Http/Controllers/Api/PlanController.php
app/Http/Controllers/Api/CheckoutController.php
app/Http/Controllers/Api/SubscriptionController.php
config/plans.php                          # Plan definitions
database/migrations/xxxx_create_plans_tables.php
```

### High Priority

```
app/Http/Controllers/Api/BillingController.php
app/Http/Controllers/Api/InvoiceController.php
app/Notifications/WelcomeNotification.php
app/Notifications/PaymentFailedNotification.php
app/Notifications/SubscriptionExpiringNotification.php
```

---

## 11. Conclusion

### Summary

The SaaS basecode has a **solid multi-tenancy foundation** with proper security measures (fail-safe scoping, audit logging, atomic operations). However, **critical components are missing** that prevent it from functioning as a complete SaaS platform:

1. **No signup flow** - Cannot onboard new customers
2. **Broken Stripe integration** - Referenced classes don't exist
3. **No plan management** - Cannot define or sell different tiers
4. **Incomplete webhook handling** - Dunning not implemented
5. **No billing portal** - Customers cannot manage subscriptions

### Recommendation

**Do NOT deploy to production** until at minimum:
- Phase 1 critical fixes are complete
- Phase 2 subscription flow is working
- Full end-to-end testing is performed

### Test Coverage Required

Before production:
- [ ] Unit tests for all new services
- [ ] Integration tests for Stripe webhooks
- [ ] E2E tests for signup → checkout → subscription flow
- [ ] Security audit for authentication/authorization
- [ ] Load testing for quota atomic operations

---

## Appendix: Quick Reference

### Environment Variables Required

```env
# JWT
JWT_SECRET=your-secret-key

# Stripe
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Tenancy
TENANCY_STRICT_SCOPE=true
TENANCY_MISSING_CONTEXT_ACTION=empty
```

### Key Commands

```bash
# Run existing tests
php artisan test --filter=Unit

# Check for missing classes
php artisan route:list  # Will show errors for broken routes
```

---

**Report Generated:** 2026-01-22
**Next Review:** After Phase 1 completion
