# Phase 2: Production Infrastructure Audit Fixes

**Date:** 2026-03-27
**Scope:** 5 tasks from the audit findings — production config, database strictness, health check, security headers, permission caching
**Source:** `docs/plans/2026-03-26-audit-fixes-implementation.md` (Tasks 11-15)
**Approach:** Refined from audit plan — validated against actual codebase, fixed AdminAuthGates caching approach

---

## Task 11: Document Production Config in .env.example [R2, R6, R7, P1, P11]

**Files:** `.env.example`

**Problem:** No documentation in .env.example about production-recommended values for queue, cache, and session drivers.

**Fix:** Add comments above the existing entries (lines 18-22) documenting that production should use redis:

```
# Production recommendation: use redis for all three
# QUEUE_CONNECTION=redis
# CACHE_DRIVER=redis
# SESSION_DRIVER=redis
```

Keep the current dev defaults (`sync`, `file`, `file`) unchanged.

**Test:** Manual verification — no automated test needed.

---

## Task 12: Enable MySQL Strict Mode [R9]

**Files:** `config/database.php:76`

**Problem:** `'strict' => false` allows MySQL to silently accept invalid queries — implicit GROUP BY behavior, zero dates, truncated data.

**Fix:** Change `'strict' => false` to `'strict' => true`.

This enables:
- `ONLY_FULL_GROUP_BY` — SELECT columns must appear in GROUP BY or be aggregated
- `STRICT_TRANS_TABLES` — reject invalid data on INSERT/UPDATE
- `NO_ZERO_DATE` / `NO_ZERO_IN_DATE` — reject zero date values
- `ERROR_FOR_DIVISION_BY_ZERO`

**Risk:** Existing queries with implicit GROUP BY or zero dates will fail. Run the full test suite and fix any broken queries as part of this task. These are queries that were silently wrong — fixing them is the point.

**Tests:** Full test suite must pass after enabling.

---

## Task 13: Comprehensive Health Check Endpoint [R3]

**Files:**
- Create: `app/Http/Controllers/HealthCheckController.php`
- Modify: `routes/web.php`

**Problem:** The existing `/up` route (Laravel 11 built-in, registered in `bootstrap/app.php:13`) returns a bare 200 OK. No component-level diagnostics for ops monitoring.

**Fix:** Add a separate `/health` endpoint that checks:
- **Database:** `DB::connection()->getPdo()` — can we connect?
- **Cache:** `Cache::put()` + `Cache::get()` — is the cache driver working?

Returns structured JSON:
```json
{
  "status": "healthy" | "degraded",
  "checks": { "database": "ok", "cache": "ok" },
  "timestamp": "2026-03-27T12:00:00+00:00"
}
```

Returns HTTP 200 if all healthy, 503 if degraded.

**Refinement from plan:** Strip error messages when `APP_DEBUG=false` to avoid leaking connection details. Return just `"failed"` instead of `"failed: Connection refused..."`.

**No auth required** — health checks must be accessible without JWT for load balancers and monitoring tools.

**Don't remove `/up`** — load balancers may already depend on it.

**Tests:** Feature test hitting `/health` and asserting JSON structure + 200 status.

---

## Task 14: Security Headers Middleware [R11, R12]

**Files:**
- Create: `app/Http/Middleware/SecurityHeaders.php`
- Modify: `bootstrap/app.php`

**Problem:** No security headers set on responses. Missing clickjacking protection, MIME sniffing prevention, HSTS.

**Fix:** Create middleware that sets:
- `X-Frame-Options: DENY` — prevent clickjacking
- `X-Content-Type-Options: nosniff` — prevent MIME sniffing
- `X-XSS-Protection: 1; mode=block` — legacy XSS filter
- `Referrer-Policy: strict-origin-when-cross-origin` — control referrer leakage
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` — HTTPS only (only set when `$request->secure()`)

**Refinement from plan:** Register on BOTH API and web middleware stacks (not just API). The admin SPA, pricing page, and checkout pages need these headers too.

In `bootstrap/app.php`:
```php
$middleware->api(append: [
    \App\Http\Middleware\IdentifyTenant::class,
    \App\Http\Middleware\AdminAuthGates::class,
    \App\Http\Middleware\SecurityHeaders::class,
]);

$middleware->web(append: [
    \App\Http\Middleware\SecurityHeaders::class,
]);
```

**Tests:** Feature test asserting response headers are present on an API request.

---

## Task 15: Cache AdminAuthGates Permission Map [S14]

**Files:** `app/Http/Middleware/AdminAuthGates.php`

**Problem:** `Role::with('permissions')->get()` runs on EVERY authenticated API request. Loads all roles and permissions from DB each time, then builds a permission-to-roles map and defines Gates.

**Fix:** Cache the permission-to-roles map for 5 minutes. Keep the existing Gate definition logic unchanged.

**Current flow (every request):**
1. `Role::with('permissions')->get()` — DB query
2. Build `$permissionsArray` map: `permission_title => [role_id, ...]`
3. Filter out `$excludePermissions`
4. `Gate::define()` for each permission — checks user's roles at runtime

**New flow:**
1. `Cache::remember('admin_permission_role_map', 300, fn() => ...)` — DB query cached 5 min
2. Same Gate definitions using the cached map
3. User's roles still checked at runtime via `$user->roles->pluck('id')`

**Why NOT per-user caching (what the original plan proposed):** The plan suggested caching each user's resolved permissions and defining gates as `fn() => true`. This changes authorization semantics — if a user's role changes, their permissions are stale until cache expires. With map-level caching, only the permission-to-roles mapping is cached; the user's actual role membership is always checked fresh.

**Cache invalidation:** Role/permission changes take up to 5 minutes to propagate. This is acceptable because:
- Admin permission changes are rare (hours/days between changes)
- The `array` cache driver in tests means no stale cache during testing

**Tests:** Existing test suite should pass (TestCase uses `'cache.default' => 'array'`).

---

## Execution Order

All tasks are independent. Execute in order:
1. **Task 11** (env docs) — trivial, no risk
2. **Task 12** (strict mode) — run first among risky tasks so we fix queries early
3. **Task 13** (health check) — new feature, standalone
4. **Task 14** (security headers) — new middleware, standalone
5. **Task 15** (permission caching) — performance fix, standalone

Each task gets its own commit. Run `vendor/bin/pint --dirty` before committing.

---

## Out of Scope

- Phase 3 (Test coverage expansion) and Phase 4 (Performance & code quality) remain for follow-up sessions
- Redis configuration (just documenting the recommendation, not installing/configuring Redis)
- Cache invalidation on permission change (future enhancement — model observer)
