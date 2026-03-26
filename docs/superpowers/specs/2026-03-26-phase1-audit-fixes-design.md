# Phase 1: Critical Security & Data Integrity Audit Fixes

**Date:** 2026-03-26
**Scope:** 10 tasks from the audit findings — security, data integrity, infrastructure, and performance
**Source:** `docs/plans/2026-03-26-audit-fixes-implementation.md` (Tasks 1-10)
**Approach:** Refined from audit plan — validated against actual codebase, scoped fixes to real issues only

---

## Task 1: Remove Hardcoded Secrets from .env.example [S1-S4]

**Files:** `.env.example`

**Problem:** Two hardcoded secrets committed to the repository:
- Line 3: `APP_KEY=base64:aVAeGqeDTte4JTJIzedrD0fQAbmIbVIfkcKzif1g418=`
- Line 64: `JWT_SECRET=pYMl9qyaW46aoOC51XOnaNw1iw0bbVeHfc4ulOkjPCu08E9ekO8pSBmQesIeDuQC`

**Fix:**
- Replace line 3 with `APP_KEY=`
- Replace line 64 with `JWT_SECRET=`
- Verify `.env` is in `.gitignore`

**Test:** Manual verification — no automated test needed.

---

## Task 2: Whitelist Scopes in FilterQueryBuilder [S6]

**Files:**
- Modify: `app/Support/FilterQueryBuilder.php:221-227`
- Create: `tests/Unit/Support/FilterQueryBuilderScopeTest.php`

**Problem:** The `scope()` method calls any method name passed via filter parameters with no validation. An attacker could invoke `delete`, `forceDelete`, or `withoutGlobalScopes` through the filter API.

**Fix:** Add a `$allowedScopes` whitelist derived from actual model scopes used in listing pages:
```php
protected static array $allowedScopes = [
    'active', 'ordered', 'pending', 'today', 'forDate', 'urgent', 'visible',
];
```

Validate each scope name against the whitelist before calling. Throw `\InvalidArgumentException` for disallowed scopes.

**Rationale for whitelist over denylist:** Denylists can be bypassed by new dangerous methods. The whitelist is restrictive by default — new scopes must be explicitly added. The scope filter operator is not currently used in any frontend filter config, so this is purely defensive.

**Tests:**
- Rejects dangerous scope names (`delete`, `forceDelete`, `withoutGlobalScopes`)
- Accepts whitelisted scope names (`active`, `ordered`)

---

## Task 3: Payment Overpayment & Negative Tip Validation [D2, D17]

**Files:**
- Modify: `app/Services/PosService.php` (processPayment method, lines 338-404)
- Create: `tests/Feature/PosPaymentValidationTest.php`

**Problem:** `processPayment` accepts any amount with no upper bound validation. A payment of $500 on a $20 balance succeeds. Negative tip amounts are also accepted.

**Fix:** Inside the `DB::transaction` callback, after extracting `$amount`, add:
1. **Overpayment guard:** If `$amount > currentBalance + 0.01`, throw `ValidationException`. The 0.01 tolerance handles floating-point rounding.
2. **Negative tip guard:** If `$tipAmount < 0`, throw `ValidationException`.

The `currentBalance` must account for tip changes: if a new tip is provided, the balance shifts by `newTip - oldTip`.

**Tests:**
- Rejects payment exceeding order balance
- Accepts payment equal to order balance
- Rejects negative tip amount

---

## Task 4: Race Conditions in Number Generation [D3, D4]

**Files:**
- Modify: `app/Services/PosService.php` (generateOrderNumber, generatePaymentNumber, generateCustomerCode — lines 487-518+)

**Problem:** The current generators use `lockForUpdate()` but rely on `whereDate('created_at', today())` which is fragile at midnight boundaries and time zone mismatches. While the callers (`createQuickOrder`, `processPayment`) wrap in `DB::transaction` so the locks work, the generators should be self-contained.

**Fix:** Wrap each generator in its own `DB::transaction` and use `like` pattern matching on the actual number format instead of date filtering:
- `generateOrderNumber`: `Order::where('order_number', 'like', "{$prefix}{$date}%")->lockForUpdate()`
- `generatePaymentNumber`: `Payment::where('payment_number', 'like', "{$prefix}{$date}%")->lockForUpdate()`
- `generateCustomerCode`: `Customer::where('customer_code', 'like', "{$prefix}%")->lockForUpdate()`

**Tests:** Existing test suite should pass. No new tests needed — race condition testing requires concurrent database connections which are impractical in Pest.

---

## Task 5: Concurrent Payment + Order Edit Lock [D5]

**Files:**
- Modify: `app/Services/PosService.php` (processPayment method)

**Problem:** `processPayment` operates on a potentially stale `$order` object. If another request modifies the order between the initial read and the payment update, data is corrupted (e.g., wrong balance calculation).

**Fix:** At the start of the `DB::transaction` callback, re-fetch the order with `lockForUpdate()`:
```php
$order = Order::where('id', $order->id)->lockForUpdate()->first();
```

This must come before all other logic, including the amount extraction. The overpayment validation (Task 3) then operates on the freshly locked order.

**Tests:** Covered by Task 3 tests — the lock is transparent to the test logic.

---

## Task 6: XSS via v-html [S5]

**Files:**
- Modify: `resources/metronic/components/customers/cards/events-and-logs/Events.vue:38`

**Problem:** 7 components use `v-html`. After review:
- `DatatableHtml.vue` — already sanitized with DOMPurify. Safe.
- `TableHeadRow.vue` — computed SVG constant. Safe.
- `Widget9.vue`, `Widget1.vue`, `Card.vue`, `Notice.vue` — component props with static content. Low risk.
- **`Events.vue:38`** — `v-html="event.event"` renders database data. Vulnerable.

**Fix:** Only `Events.vue` needs a fix. Replace `v-html="event.event"` with `v-html="sanitize(event.event)"` and add DOMPurify sanitization (already a project dependency — used in `DatatableHtml.vue`).

**Tests:** No automated test — frontend XSS testing requires E2E browser tests which are out of scope for Phase 1.

---

## Task 7: Validation Gaps [S16, D16, D17]

**Files:**
- Modify: `app/Http/Requests/StoreOrderRequest.php`
- Modify: `app/Services/PosService.php` (updateOrderStatus method, lines 303-333)

### 7a: Monetary field bounds

**Problem:** `discount_amount` has `min:0` but no `max`. `tip_amount` same. An attacker could submit `discount_amount: 99999999`.

**Fix:** Add max constraints:
- `discount_amount` → `'max:99999.99'`
- `tip_amount` → `'max:9999.99'`
- `discount_percentage` (if present) → `'max:100'`

### 7b: Status transition validation

**Problem:** `updateOrderStatus` accepts any status transition — you can go from Delivered back to Pending.

**Fix:** Add a `$validTransitions` map:
```
Pending → [Washing, Cancelled]
Washing → [Drying, Pending, Cancelled]
Drying → [Ready Area, Washing, Cancelled]
Ready Area → [Delivered, Drying]
Delivered → [] (terminal)
Cancelled → [] (terminal)
```

Throw `ValidationException` for disallowed transitions.

**Important edge case:** `processPayment` auto-transitions to Delivered on full payment (line 389) by calling `updateOrderStatus`. This could be rejected if the order is in Washing/Drying/Pending. Solution: add an optional `$force` parameter to `updateOrderStatus` that bypasses transition validation for system-initiated transitions (payment completion). The `$force` flag is only used internally — never exposed via API.

**Tests:**
- Valid transitions succeed
- Invalid transitions throw ValidationException
- Payment-triggered delivery works from any non-terminal state

---

## Task 8: Soft-Delete Guards & Loyalty Race Conditions [D6-D10]

**Files:**
- Modify: `app/Services/LoyaltyService.php`

### 8a: Soft-delete guard

**Problem:** `awardPointsForOrder` checks `if (!$customer) return null` but not `$customer->trashed()`. A soft-deleted customer could earn loyalty points.

**Fix:** Add `if ($customer->trashed()) return null;` after the null check on line 45.

### 8b: addBonusPoints transaction

**Problem:** `addBonusPoints` (lines 116-133) is not in a transaction. Two concurrent bonus point additions read the same `loyalty_points` balance, both add points, and the second write overwrites the first — losing points.

**Fix:** Wrap in `DB::transaction` with `lockForUpdate` on the customer row:
```php
return DB::transaction(function () use ($customer, $points, $reason) {
    $customer = Customer::where('id', $customer->id)->lockForUpdate()->first();
    // ... existing logic using fresh customer data
});
```

**Tests:**
- Soft-deleted customer does not earn points
- Bonus points update correctly

---

## Task 9: CORS Restriction & Rate Limiting [S11-S13]

**Files:**
- Modify: `config/cors.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/api.php` (apply rate limiters to route groups)

### 9a: CORS

**Problem:** `'allowed_methods' => ['*']` exposes all HTTP methods including TRACE, CONNECT.

**Fix:** Change to `['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']`.

### 9b: Rate limiting

**Problem:** Only a general `api` limiter at 600/min. No protection against write-heavy abuse or export scraping.

**Fix:** Add two rate limiters in `AppServiceProvider`:
- `writes` — 120/min per user (for mutation endpoints)
- `exports` — 10/min per user (for report exports)

Apply in `routes/api.php`:
- `throttle:writes` on POST/PUT/DELETE route groups
- `throttle:exports` on `reports/*/export` routes

**Tests:** Existing test suite should pass. Rate limiter testing is typically done via integration tests.

---

## Task 10: Missing Database Indexes [P6, P13, D18]

**Files:**
- Create: migration `add_missing_performance_indexes`

**Verified no duplicates exist.** All indexes are net-new:

| Table | Index | Purpose |
|-------|-------|---------|
| `service_prices` | `service_id` | JOIN lookups in calculateOrderTotals |
| `service_prices` | `item_id` | JOIN lookups in calculateOrderTotals |
| `order_items` | `(tenant_id, item_id)` | Tenant-scoped item queries |
| `order_items` | `(tenant_id, service_id)` | Tenant-scoped service queries |
| `payments` | `tenant_id` | Tenant isolation queries |
| `payments` | `(customer_id, payment_date)` | Customer payment history |
| `orders` | `(tenant_id, processing_status_id, order_date)` | POS Kanban board queries |

Migration will use try/catch per index to be idempotent (safe to re-run).

**Tests:** Run `php artisan migrate` successfully. Existing test suite passes.

---

## Execution Order

Tasks should be executed in this order due to dependencies:
1. **Task 1** (secrets) — standalone, no dependencies
2. **Task 2** (scope whitelist) — standalone
3. **Task 5** (order lock in processPayment) — must come before Task 3
4. **Task 3** (overpayment validation) — depends on Task 5's lock being in place
5. **Task 4** (number generation race conditions) — standalone
6. **Task 6** (XSS) — standalone
7. **Task 7** (validation gaps + status transitions) — standalone
8. **Task 8** (soft-delete + loyalty) — standalone
9. **Task 9** (CORS + rate limiting) — standalone
10. **Task 10** (indexes) — standalone, run last to avoid migration issues during development

Each task gets its own commit. All tasks run `vendor/bin/pint --dirty` before committing.

---

## Out of Scope (Phase 2-4)

- Production infrastructure (health check, security headers, permission caching)
- Test coverage expansion (PosService tests, controller tests, validation tests, integration tests)
- Performance (remove duplicate editors, replace moment.js, N+1 fixes)
- Code quality (console.log removal, commented code, type checking)

These are tracked in the original audit plan and will be addressed in follow-up sessions.
