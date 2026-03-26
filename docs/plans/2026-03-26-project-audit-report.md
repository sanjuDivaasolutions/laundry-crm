# Laundry CRM - Comprehensive Project Audit Report

**Date:** 2026-03-26
**Type:** Findings Report (No Code Fixes)
**Approach:** Risk-based sampling across 6 domains
**Scope:** Single-tenant mode (SaaS dormant)

---

## Executive Summary

| Domain | Critical | High | Medium | Low | Info |
|--------|----------|------|--------|-----|------|
| Security & Auth | 4 | 4 | 10 | 2 | 0 |
| Data Integrity | 5 | 6 | 4 | 5 | 2 |
| Performance | 1 | 4 | 8 | 0 | 0 |
| Code Quality | 1 | 5 | 10 | 1 | 0 |
| Test Coverage | 9 | 9 | 7 | 0 | 0 |
| Production Readiness | 3 | 7 | 5 | 0 | 0 |
| **TOTAL** | **23** | **35** | **44** | **8** | **2** |

---

## Top 10 Critical Findings (Must Fix Before Production)

### 1. JWT_SECRET hardcoded in .env.example
**Domain:** Security | **File:** `.env.example:64`
Actual JWT secret committed to version control. Can forge tokens if used in production.
**Fix:** Replace with placeholder, rotate secret, run `php artisan jwt:secret`.

### 2. Payment overpayment vulnerability
**Domain:** Data Integrity | **File:** `app/Services/PosService.php:369-385`
No validation that payment amount doesn't exceed order balance. Can create negative balances.
**Fix:** Add check: `if ($amount > $order->balance_amount) abort(422)`.

### 3. Race condition in order/payment number generation
**Domain:** Data Integrity | **File:** `app/Services/PosService.php:487-518`
`lockForUpdate()` only locks today's records but uniqueness spans entire tenant. Concurrent requests can generate duplicate numbers.
**Fix:** Lock across broader date range or use database sequence.

### 4. Concurrent payment + order edit race condition
**Domain:** Data Integrity | **File:** `app/Services/PosService.php:338-404`
No optimistic locking. Payment can mark order as Paid while order total is being changed simultaneously.
**Fix:** Add version column or `lockForUpdate()` within transaction.

### 5. Synchronous queue blocks all requests
**Domain:** Production | **File:** `.env.example:21`
`QUEUE_CONNECTION=sync` means emails, exports, and webhooks execute during HTTP request.
**Fix:** Set `QUEUE_CONNECTION=redis`, run `php artisan queue:work`.

### 6. No backup strategy
**Domain:** Production | **Severity:** Critical
No `spatie/laravel-backup`, no automated backups, no recovery plan.
**Fix:** Install backup package, configure daily DB backups to S3.

### 7. 94.7% controllers have zero test coverage
**Domain:** Testing | **File:** `tests/`
Only 2 of 38 controllers have dedicated tests. 122 Form Requests have zero validation tests.
**Fix:** Prioritize PosService, CustomerController, ServiceController, Form Request tests.

### 8. Multiple XSS vectors via v-html
**Domain:** Security | **Files:** 5+ Vue components
`v-html` binds unsanitized data from database. Events component particularly dangerous.
**Fix:** Replace with text binding or apply DOMPurify.

### 9. FilterQueryBuilder scope operator — unsafe method invocation
**Domain:** Security | **File:** `app/Support/FilterQueryBuilder.php:221-227`
Calls `$query->{$field}()` where $field comes from user input. Any Eloquent scope can be invoked.
**Fix:** Whitelist allowed scopes explicitly.

### 10. Database strict mode disabled
**Domain:** Production | **File:** `config/database.php:76`
`'strict' => false` allows invalid data, hides bugs.
**Fix:** Enable strict mode, test all queries.

---

## Domain 1: Security & Authentication

### Critical
- **S1:** JWT_SECRET hardcoded in .env.example (`.env.example:64`)
- **S2:** Stripe test keys in committed .env (`.env:75-76`)
- **S3:** APP_DEBUG potentially true in production (`.env:4`)
- **S4:** Database credentials in plain .env with root user

### High
- **S5:** 5+ Vue components use v-html without sanitization
- **S6:** FilterQueryBuilder scope operator allows arbitrary method calls
- **S7:** whereRaw patterns in OrderBoardApiController
- **S8:** Stripe API calls lack try-catch error handling

### Medium
- **S9:** No JWT refresh endpoint (forces re-login on expiry)
- **S10:** Logout doesn't invalidate tokens server-side
- **S11:** CORS allows all HTTP methods (`['*']`)
- **S12:** No API rate limiting beyond login (600/min global only)
- **S13:** Login rate limiting allows 7,200 attempts/day
- **S14:** AdminAuthGates loads all permissions on every request (no caching)
- **S15:** JWT stored in localStorage (XSS-vulnerable)
- **S16:** Missing validation max on discount_amount, tip_amount
- **S17:** User interface includes password field
- **S18:** Missing /keys endpoint bypasses auth without documentation

---

## Domain 2: Data Integrity

### Critical
- **D1:** Customer code not tenant-scoped unique (`customers` migration)
- **D2:** Payment overpayment vulnerability (PosService:369)
- **D3:** Race condition in order number generation (PosService:487)
- **D4:** Race condition in payment number generation (PosService:504)
- **D5:** Payment+order edit race condition (PosService:338)

### High
- **D6:** Soft-delete: OrderItems not properly cascading
- **D7:** Soft-delete: DeliverySchedules not scoped with withTrashed
- **D8:** Payment can reference soft-deleted order
- **D9:** Loyalty points awarded to soft-deleted customer
- **D10:** Loyalty bonus points race condition (no transaction wrapper)
- **D11:** Order item barcode uniqueness not validated before save

### Medium
- **D12:** Payment status not synced when order total changes
- **D13:** Missing ON DELETE rule on Payment.customer_id FK
- **D14:** Customer search in OrderBoardApiController not tenant-scoped
- **D15:** Order payment panel crashes if customer soft-deleted

### Low
- **D16:** No prevention of backward status transitions (Delivered->Pending)
- **D17:** Tip amount can be negative
- **D18:** Missing index on Payment.customer_id
- **D19:** Processing status validation not tenant-scoped
- **D20:** Enum inconsistency: `Ready` case = `'Ready Area'` value

---

## Domain 3: Performance

### Critical
- **P1:** QUEUE_CONNECTION=sync blocks all requests (emails, exports)

### High
- **P2:** Triple rich text editors bundled (CKEditor + TinyMCE + Quill = ~500KB)
- **P3:** moment.js included (~60KB, should use dayjs ~2KB)
- **P4:** ServicePrice lookups in loops (N+1 in PosService)
- **P5:** Report endpoints return unbounded result sets (no pagination)

### Medium
- **P6:** Missing composite indexes on service_prices, order_items, payments
- **P7:** N+1 in CustomerApiController index (no eager loading)
- **P8:** POS board loads all today's orders then groups in PHP (should use DB groupBy)
- **P9:** ReportService runs separate queries instead of batched aggregates
- **P10:** TenantCacheService built but never used in controllers
- **P11:** Cache driver set to file (slow for production)
- **P12:** API Resources return deeply nested relationships
- **P13:** Missing tenant_id index on order_items table

---

## Domain 4: Code Quality

### Critical
- **Q1:** POS Board Index.vue is 1,744 lines (god component, 50+ methods)

### High
- **Q2:** 45 controllers duplicate identical CRUD boilerplate
- **Q3:** `gettype()` used instead of `is_array()` (type checking anti-pattern)
- **Q4:** TODO: Email dispatch not implemented (AnnouncementApiController:67)
- **Q5:** TODO: 5 ImportService methods unimplemented
- **Q6:** Duplicated store/service price sync logic across controllers

### Medium
- **Q7:** 62 Vue store files with identical boilerplate patterns
- **Q8:** Zero TypeScript in 91 Vue components (only 6.6% coverage)
- **Q9:** 6+ console.log statements left in production code
- **Q10:** Silent error swallowing in FormSelectAjax.vue
- **Q11:** Inconsistent error handling patterns across controllers
- **Q12:** Missing return type declarations across methods
- **Q13:** PosService is 540 lines (should be 3 services)
- **Q14:** HasEntitlements trait is 527 lines (should be a service)
- **Q15:** Commented-out code blocks in ControllerRequest, QuotationRequest
- **Q16:** Inconsistent gate authorization pattern across controllers
- **Q17:** Hardcoded user-facing strings instead of i18n

---

## Domain 5: Test Coverage

### Critical
- **T1:** 0/122 Form Requests have validation tests
- **T2:** 36/38 controllers have no dedicated tests (94.7%)
- **T3:** PosService (540 lines, core feature) has no dedicated tests
- **T4:** CustomerApiController (core entity) completely untested
- **T5:** ImportService (data integrity risk) has zero tests
- **T6:** ServiceApiController (pricing model) untested
- **T7:** UsersApiController (security-critical) untested
- **T8:** DeliveryScheduleApiController untested
- **T9:** No end-to-end integration tests for business workflows

### High
- **T10:** LoyaltyService only has tip-related tests (no tier, no redemption)
- **T11:** TenantCacheService (356 lines) untested
- **T12:** No concurrent access tests (race conditions unverified)
- **T13:** No API pagination/filtering tests
- **T14:** No edge case tests (boundary values, negative amounts)
- **T15:** ReportService has no unit tests (only via controller)
- **T16:** No webhook retry logic tests
- **T17:** Zero import validation tests
- **T18:** Missing authorization matrix tests

### Medium
- **T19:** No database constraint tests (unique, FK, cascade)
- **T20:** Limited soft delete behavior coverage
- **T21:** No performance tests (N+1 detection)
- **T22:** No file upload tests
- **T23:** Incomplete TenantService tests
- **T24:** No LanguageService unit tests
- **T25:** No API documentation compliance tests

---

## Domain 6: Production Readiness

### Critical
- **R1:** No backup strategy (no spatie/laravel-backup, no automation)
- **R2:** Queue runs synchronously (QUEUE_CONNECTION=sync)
- **R3:** Health check endpoint only checks app boot (not DB, cache, queue)

### High
- **R4:** No error tracking service (Sentry, Bugsnag, Flare)
- **R5:** No deployment script or documentation
- **R6:** File-based session driver (breaks with load balancers)
- **R7:** File-based cache driver (slow, not scalable)
- **R8:** Email mailer set to log (no emails actually sent)
- **R9:** Database strict mode disabled
- **R10:** CI/CD pipeline uses PHP 8.0 (app requires 8.2)

### Medium
- **R11:** No security headers middleware (X-Frame-Options, HSTS, CSP)
- **R12:** SESSION_SECURE_COOKIE not set (HTTPS cookies)
- **R13:** Redis password is null (not production-ready)
- **R14:** Local file storage only (no S3 for scaling)
- **R15:** Missing rate limits on export and search endpoints

---

## Recommended Fix Priority

### Week 1: Security & Data Critical
1. Remove hardcoded secrets from .env.example
2. Fix payment overpayment validation
3. Fix order/payment number race conditions
4. Fix payment+order edit race condition
5. Fix FilterQueryBuilder scope whitelist
6. Fix v-html XSS vectors

### Week 2: Production Infrastructure
7. Configure Redis for queue/cache/session
8. Set up backup strategy
9. Enable database strict mode
10. Configure error monitoring (Sentry/Flare)
11. Set up proper email driver
12. Create deployment documentation

### Week 3: Test Coverage (Critical)
13. Write Form Request validation tests (top 20 requests)
14. Write PosService tests
15. Write CustomerController tests
16. Write end-to-end integration tests
17. Write concurrent access tests

### Week 4: Performance & Quality
18. Remove 2 of 3 rich text editors
19. Replace moment.js with dayjs
20. Add missing database indexes
21. Fix N+1 queries in PosService
22. Refactor POS Board Index.vue into smaller components
23. Extract base ResourceController for CRUD deduplication
