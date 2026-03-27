# Phase 2: Production Infrastructure Audit Fixes

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add production infrastructure — config documentation, MySQL strict mode, health check endpoint, security headers, and permission caching.

**Architecture:** 5 independent tasks. Each is a standalone fix with its own commit. No dependencies between tasks.

**Tech Stack:** Laravel 11, PHP 8.2, Pest v3, MySQL

---

## File Map

| File | Task | Changes |
|------|------|---------|
| `.env.example` | 1 | Add production config comments |
| `config/database.php` | 2 | Enable strict mode |
| `app/Http/Controllers/HealthCheckController.php` | 3 | New controller |
| `routes/web.php` | 3 | Register `/health` route |
| `tests/Feature/HealthCheckTest.php` | 3 | New test file |
| `app/Http/Middleware/SecurityHeaders.php` | 4 | New middleware |
| `bootstrap/app.php` | 4 | Register middleware on API + web |
| `tests/Feature/SecurityHeadersTest.php` | 4 | New test file |
| `app/Http/Middleware/AdminAuthGates.php` | 5 | Cache permission-to-roles map |

---

### Task 1: Document Production Config in .env.example

**Files:**
- Modify: `.env.example:17-23`

- [ ] **Step 1: Add production recommendation comments**

In `.env.example`, replace lines 17-23:

```
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

with:

```
BROADCAST_DRIVER=log
# Production recommendation: use redis for queue, cache, and session
# QUEUE_CONNECTION=redis
# CACHE_DRIVER=redis
# SESSION_DRIVER=redis
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

- [ ] **Step 2: Commit**

```bash
git add .env.example
git commit -m "docs: document production queue/cache/session configuration in .env.example"
```

---

### Task 2: Enable MySQL Strict Mode

**Files:**
- Modify: `config/database.php:76`

- [ ] **Step 1: Enable strict mode**

In `config/database.php`, replace line 76:

```php
            'strict' => false,
```

with:

```php
            'strict' => true,
```

- [ ] **Step 2: Run full test suite**

Run: `php artisan test --compact`
Expected: All PASS. If any tests fail due to strict mode (GROUP BY violations, zero dates), fix the underlying queries before proceeding.

- [ ] **Step 3: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add config/database.php
git commit -m "fix(production): enable MySQL strict mode"
```

If query fixes were needed, include those files in the commit and adjust the message:

```bash
git add config/database.php app/
git commit -m "fix(production): enable MySQL strict mode and fix non-compliant queries"
```

---

### Task 3: Comprehensive Health Check Endpoint

**Files:**
- Create: `app/Http/Controllers/HealthCheckController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/HealthCheckTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest HealthCheckTest`

- [ ] **Step 2: Write failing test**

Replace the contents of `tests/Feature/HealthCheckTest.php` with:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns healthy status with correct JSON structure', function () {
    $response = $this->getJson('/health');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'checks' => [
                'database',
                'cache',
            ],
            'timestamp',
        ])
        ->assertJson([
            'status' => 'healthy',
            'checks' => [
                'database' => 'ok',
                'cache' => 'ok',
            ],
        ]);
});

it('does not require authentication', function () {
    $response = $this->getJson('/health');

    $response->assertOk();
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/HealthCheckTest.php`
Expected: FAIL (route `/health` not defined)

- [ ] **Step 4: Create the HealthCheckController**

Run: `php artisan make:controller HealthCheckController --invokable --no-interaction`

Then replace the contents of `app/Http/Controllers/HealthCheckController.php` with:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [];
        $debug = config('app.debug');

        // Database connectivity
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = $debug ? 'failed: '.$e->getMessage() : 'failed';
        }

        // Cache read/write
        try {
            Cache::put('health-check', true, 10);
            Cache::get('health-check');
            $checks['cache'] = 'ok';
        } catch (\Exception $e) {
            $checks['cache'] = $debug ? 'failed: '.$e->getMessage() : 'failed';
        }

        $allOk = ! collect($checks)->contains(fn ($v) => str_starts_with($v, 'failed'));

        return response()->json([
            'status' => $allOk ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allOk ? 200 : 503);
    }
}
```

- [ ] **Step 5: Register route in routes/web.php**

In `routes/web.php`, add this line after the existing `Route::get('/', ...)` block (after line 44):

```php
Route::get('/health', \App\Http\Controllers\HealthCheckController::class);
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/HealthCheckTest.php`
Expected: All PASS

- [ ] **Step 7: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Controllers/HealthCheckController.php routes/web.php tests/Feature/HealthCheckTest.php
git commit -m "feat(production): add comprehensive health check endpoint at /health"
```

---

### Task 4: Security Headers Middleware

**Files:**
- Create: `app/Http/Middleware/SecurityHeaders.php`
- Modify: `bootstrap/app.php:38-41`
- Create: `tests/Feature/SecurityHeadersTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest SecurityHeadersTest`

- [ ] **Step 2: Write failing test**

Replace the contents of `tests/Feature/SecurityHeadersTest.php` with:

```php
<?php

it('includes security headers on web responses', function () {
    $response = $this->get('/health');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('includes security headers on API responses', function () {
    $response = $this->getJson('/api/v1/test');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/SecurityHeadersTest.php`
Expected: FAIL (headers not present)

- [ ] **Step 4: Create the SecurityHeaders middleware**

Run: `php artisan make:middleware SecurityHeaders --no-interaction`

Then replace the contents of `app/Http/Middleware/SecurityHeaders.php` with:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
```

- [ ] **Step 5: Register middleware on API and web stacks**

In `bootstrap/app.php`, replace lines 38-41:

```php
        $middleware->api(append: [
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\AdminAuthGates::class,
        ]);
```

with:

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

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/SecurityHeadersTest.php`
Expected: All PASS

- [ ] **Step 7: Run full test suite**

Run: `php artisan test --compact`
Expected: All PASS

- [ ] **Step 8: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Middleware/SecurityHeaders.php bootstrap/app.php tests/Feature/SecurityHeadersTest.php
git commit -m "feat(security): add security headers middleware on API and web stacks"
```

---

### Task 5: Cache AdminAuthGates Permission Map

**Files:**
- Modify: `app/Http/Middleware/AdminAuthGates.php`

- [ ] **Step 1: Add Cache import and wrap the query in Cache::remember**

Replace the entire contents of `app/Http/Middleware/AdminAuthGates.php` with:

```php
<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class AdminAuthGates
{
    public function handle($request, Closure $next)
    {
        $user = adminAuth()->user();

        if (! $user) {
            return $next($request);
        }

        $permissionsArray = Cache::remember('admin_permission_role_map', 300, function () {
            $roles = Role::with('permissions')->get();
            $map = [];
            $excludePermissions = config('auth.protected_permissions', []);

            foreach ($roles as $role) {
                foreach ($role->permissions as $permission) {
                    if (! in_array($permission->title, $excludePermissions)) {
                        $map[$permission->title][] = $role->id;
                    }
                }
            }

            return $map;
        });

        foreach ($permissionsArray as $title => $roles) {
            Gate::define($title, function (User $user) use ($roles) {
                return count(array_intersect($user->roles->pluck('id')->toArray(), $roles)) > 0;
            });
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Run full test suite**

Run: `php artisan test --compact`
Expected: All PASS (test environment uses `'cache.default' => 'array'` so no stale cache issues)

- [ ] **Step 3: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Middleware/AdminAuthGates.php
git commit -m "perf(security): cache permission-to-roles map for 5 minutes in AdminAuthGates"
```

---

## Final Verification

After all tasks are complete:

- [ ] **Run full test suite:** `php artisan test --compact`
- [ ] **Run pint:** `vendor/bin/pint --dirty`
- [ ] **Verify build:** `npm run build`
- [ ] **Test health endpoint manually:** `curl http://localhost:8000/health`
