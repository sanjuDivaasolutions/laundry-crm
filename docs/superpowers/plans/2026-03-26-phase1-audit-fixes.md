# Phase 1: Critical Security & Data Integrity Audit Fixes

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix the 10 highest-severity audit findings — hardcoded secrets, scope injection, payment vulnerabilities, race conditions, XSS, validation gaps, and missing indexes.

**Architecture:** Each task is an independent fix with its own commit. Execution order respects one dependency: Task 3 (order lock) must precede Task 4 (overpayment validation) since the lock ensures validation reads fresh data. All other tasks are standalone.

**Tech Stack:** Laravel 11, PHP 8.2, Pest v3, Vue 3, MySQL

---

## File Map

| File | Tasks | Changes |
|------|-------|---------|
| `.env.example` | 1 | Clear hardcoded APP_KEY and JWT_SECRET |
| `app/Support/FilterQueryBuilder.php` | 2 | Add scope whitelist + validation |
| `tests/Unit/Support/FilterQueryBuilderScopeTest.php` | 2 | New test file |
| `app/Services/PosService.php` | 3, 4, 5, 6 | Order lock, overpayment guard, number generators, status transitions |
| `tests/Feature/PosPaymentValidationTest.php` | 4 | New test file |
| `tests/Feature/Services/PosStatusTransitionTest.php` | 6 | New test file |
| `resources/metronic/components/customers/cards/events-and-logs/Events.vue` | 7 | DOMPurify sanitization |
| `app/Http/Requests/StoreOrderRequest.php` | 8 | Add max constraints |
| `app/Services/LoyaltyService.php` | 8 | Soft-delete guard + transaction wrap |
| `tests/Feature/Services/LoyaltyServiceTest.php` | 8 | New test file |
| `config/cors.php` | 9 | Restrict allowed methods |
| `app/Providers/AppServiceProvider.php` | 9 | Add write/export rate limiters |
| `routes/api.php` | 9 | Apply throttle middleware to route groups |
| Migration file (auto-generated) | 10 | Add 7 missing indexes |

---

### Task 1: Remove Hardcoded Secrets from .env.example

**Files:**
- Modify: `.env.example:3,64`

- [ ] **Step 1: Clear the hardcoded APP_KEY**

In `.env.example`, replace line 3:

```
APP_KEY=base64:aVAeGqeDTte4JTJIzedrD0fQAbmIbVIfkcKzif1g418=
```

with:

```
APP_KEY=
```

- [ ] **Step 2: Clear the hardcoded JWT_SECRET**

In `.env.example`, replace line 64:

```
JWT_SECRET=pYMl9qyaW46aoOC51XOnaNw1iw0bbVeHfc4ulOkjPCu08E9ekO8pSBmQesIeDuQC
```

with:

```
JWT_SECRET=
```

- [ ] **Step 3: Verify .env is in .gitignore**

Run: `grep "^\.env$" .gitignore`
Expected: `.env` on line 19 (already confirmed present)

- [ ] **Step 4: Commit**

```bash
git add .env.example
git commit -m "fix(security): remove hardcoded APP_KEY and JWT_SECRET from .env.example"
```

---

### Task 2: Whitelist Scopes in FilterQueryBuilder

**Files:**
- Modify: `app/Support/FilterQueryBuilder.php:221-227`
- Create: `tests/Unit/Support/FilterQueryBuilderScopeTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest --unit Support/FilterQueryBuilderScopeTest`

- [ ] **Step 2: Write failing tests**

Replace the contents of `tests/Unit/Support/FilterQueryBuilderScopeTest.php` with:

```php
<?php

use App\Support\FilterQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function () {
    $this->builder = new FilterQueryBuilder();
});

it('rejects dangerous scope names', function (string $scopeName) {
    $query = Mockery::mock(Builder::class);

    $filter = [
        'operator' => 'scope',
        'query_1' => [$scopeName],
    ];

    $this->builder->scope($filter, $query);
})->with([
    'delete',
    'forceDelete',
    'withoutGlobalScopes',
    'truncate',
    'restore',
])->throws(\InvalidArgumentException::class);

it('allows whitelisted scope names', function (string $scopeName) {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive($scopeName)->once()->andReturnSelf();

    $filter = [
        'operator' => 'scope',
        'query_1' => [$scopeName],
    ];

    $this->builder->scope($filter, $query);

    // If we reach here without exception, it passed
    expect(true)->toBeTrue();
})->with([
    'active',
    'ordered',
    'pending',
    'today',
    'urgent',
]);
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --compact tests/Unit/Support/FilterQueryBuilderScopeTest.php`
Expected: The "rejects dangerous scope names" tests FAIL (currently no validation exists)

- [ ] **Step 4: Add the whitelist and validation to FilterQueryBuilder**

In `app/Support/FilterQueryBuilder.php`, replace the `scope` method (lines 221-227):

```php
    /**
     * Allowed scope names that can be invoked via the filter system.
     * Add new safe scopes here as needed.
     */
    protected static array $allowedScopes = [
        'active', 'ordered', 'pending', 'today', 'forDate', 'urgent', 'visible',
    ];

    public function scope($filter, $query)
    {
        $fields = $filter['query_1'];
        foreach ($fields as $field) {
            if (! in_array($field, static::$allowedScopes, true)) {
                throw new \InvalidArgumentException("Scope '{$field}' is not allowed in filters.");
            }
            $query->{$field}();
        }
    }
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --compact tests/Unit/Support/FilterQueryBuilderScopeTest.php`
Expected: All PASS

- [ ] **Step 6: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Support/FilterQueryBuilder.php tests/Unit/Support/FilterQueryBuilderScopeTest.php
git commit -m "fix(security): whitelist allowed scopes in FilterQueryBuilder"
```

---

### Task 3: Add Order Lock in processPayment

**Files:**
- Modify: `app/Services/PosService.php:338-404`

This must be done before Task 4 (overpayment validation) so the validation operates on freshly locked data.

- [ ] **Step 1: Add lockForUpdate at the start of the transaction**

In `app/Services/PosService.php`, replace lines 340-341:

```php
        return DB::transaction(function () use ($order, $paymentData) {
            $amount = (float) $paymentData['amount'];
```

with:

```php
        return DB::transaction(function () use ($order, $paymentData) {
            // Lock order row to prevent concurrent modifications
            $order = Order::where('id', $order->id)->lockForUpdate()->first();

            if (! $order) {
                throw new \RuntimeException('Order not found.');
            }

            $amount = (float) $paymentData['amount'];
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --compact`
Expected: All PASS (the lock is transparent to existing test logic)

- [ ] **Step 3: Commit**

```bash
vendor/bin/pint --dirty
git add app/Services/PosService.php
git commit -m "fix(data): add row lock in processPayment to prevent concurrent modifications"
```

---

### Task 4: Payment Overpayment & Negative Tip Validation

**Files:**
- Modify: `app/Services/PosService.php:338-404`
- Create: `tests/Feature/PosPaymentValidationTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest PosPaymentValidationTest`

- [ ] **Step 2: Write failing tests**

Replace the contents of `tests/Feature/PosPaymentValidationTest.php` with:

```php
<?php

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PosService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

it('rejects payment exceeding order balance', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 80.00,
        'balance_amount' => 20.00,
        'payment_status' => PaymentStatusEnum::Partial,
    ]);

    $posService = app(PosService::class);

    $posService->processPayment($order, [
        'amount' => 50.00,
        'payment_method' => 'cash',
    ]);
})->throws(ValidationException::class, 'Payment amount');

it('accepts payment equal to order balance', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 80.00,
        'balance_amount' => 20.00,
        'payment_status' => PaymentStatusEnum::Partial,
    ]);

    $posService = app(PosService::class);

    $result = $posService->processPayment($order, [
        'amount' => 20.00,
        'payment_method' => 'cash',
    ]);

    expect($result['order']->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('accepts partial payment within balance', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 0,
        'balance_amount' => 100.00,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    $posService = app(PosService::class);

    $result = $posService->processPayment($order, [
        'amount' => 50.00,
        'payment_method' => 'cash',
    ]);

    expect($result['order']->payment_status)->toBe(PaymentStatusEnum::Partial);
    expect((float) $result['order']->balance_amount)->toBe(50.00);
});

it('rejects negative tip amount', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'total_amount' => 100.00,
        'paid_amount' => 0,
        'balance_amount' => 100.00,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    $posService = app(PosService::class);

    $posService->processPayment($order, [
        'amount' => 50.00,
        'payment_method' => 'cash',
        'tip_amount' => -10.00,
    ]);
})->throws(ValidationException::class, 'Tip amount');
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/PosPaymentValidationTest.php`
Expected: "rejects payment exceeding" and "rejects negative tip" FAIL (no validation exists yet)

- [ ] **Step 4: Add validation to processPayment**

In `app/Services/PosService.php`, after the `$amount` line (which now follows the lockForUpdate from Task 3), add the validation block. The section should look like:

```php
            $amount = (float) $paymentData['amount'];

            // Validate tip amount
            $tipAmount = (float) ($paymentData['tip_amount'] ?? 0);
            if ($tipAmount < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tip_amount' => ['Tip amount cannot be negative.'],
                ]);
            }

            // Validate payment doesn't exceed balance
            $currentBalance = (float) $order->balance_amount;
            if ($tipAmount > 0) {
                // Tip changes the total and therefore the balance
                $currentBalance += $tipAmount - (float) $order->tip_amount;
            }
            if ($amount > $currentBalance + 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => ["Payment amount ({$amount}) exceeds order balance ({$currentBalance})."],
                ]);
            }

            // Handle tip amount if provided
```

Also remove the old `$tipAmount` declaration that was on the next line (previously line 344), since we now declare it above. The existing tip handling block starting with `if ($tipAmount > 0) {` on the original line 345 should still use this same `$tipAmount` variable — just remove the duplicate declaration line:

```php
            // Handle tip amount if provided
            if ($tipAmount > 0) {
                $newTotalAmount = (float) $order->total_amount + $tipAmount - (float) $order->tip_amount;
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/PosPaymentValidationTest.php`
Expected: All PASS

- [ ] **Step 6: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Services/PosService.php tests/Feature/PosPaymentValidationTest.php
git commit -m "fix(data): validate payment amount and tip in processPayment"
```

---

### Task 5: Fix Race Conditions in Number Generation

**Files:**
- Modify: `app/Services/PosService.php:436-518`

- [ ] **Step 1: Rewrite generateCustomerCode with self-contained transaction**

In `app/Services/PosService.php`, replace the `generateCustomerCode` method (lines 436-445):

```php
    protected function generateCustomerCode(): string
    {
        $prefix = 'CUST';

        return DB::transaction(function () use ($prefix) {
            $lastCustomer = Customer::where('customer_code', 'like', "{$prefix}%")
                ->orderBy('customer_code', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = $lastCustomer
                ? ((int) substr($lastCustomer->customer_code, -6)) + 1
                : 1;

            return sprintf('%s%06d', $prefix, $sequence);
        });
    }
```

- [ ] **Step 2: Rewrite generateOrderNumber with pattern matching**

In `app/Services/PosService.php`, replace the `generateOrderNumber` method (lines 487-499):

```php
    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('ymd');

        return DB::transaction(function () use ($prefix, $date) {
            $lastOrder = Order::where('order_number', 'like', "{$prefix}{$date}%")
                ->orderBy('order_number', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = $lastOrder
                ? ((int) substr($lastOrder->order_number, -4)) + 1
                : 1;

            return sprintf('%s%s%04d', $prefix, $date, $sequence);
        });
    }
```

- [ ] **Step 3: Rewrite generatePaymentNumber with pattern matching**

In `app/Services/PosService.php`, replace the `generatePaymentNumber` method (lines 504-518):

```php
    protected function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('ymd');

        return DB::transaction(function () use ($prefix, $date) {
            $lastPayment = Payment::where('payment_number', 'like', "{$prefix}{$date}%")
                ->orderBy('payment_number', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = $lastPayment
                ? ((int) substr($lastPayment->payment_number, -4)) + 1
                : 1;

            return sprintf('%s%s%04d', $prefix, $date, $sequence);
        });
    }
```

- [ ] **Step 4: Run existing tests**

Run: `php artisan test --compact`
Expected: All PASS

- [ ] **Step 5: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Services/PosService.php
git commit -m "fix(data): fix race conditions in order/payment/customer number generation"
```

---

### Task 6: Add Status Transition Validation

**Files:**
- Modify: `app/Services/PosService.php:303-333`
- Create: `tests/Feature/Services/PosStatusTransitionTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Services/PosStatusTransitionTest`

- [ ] **Step 2: Write failing tests**

Replace the contents of `tests/Feature/Services/PosStatusTransitionTest.php` with:

```php
<?php

use App\Enums\ProcessingStatusEnum;
use App\Models\Order;
use App\Models\ProcessingStatus;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PosService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    $this->posService = app(PosService::class);
});

it('allows valid forward transitions', function (string $from, string $to) {
    $fromStatus = ProcessingStatus::where('status_name', $from)->first();
    $toStatus = ProcessingStatus::where('status_name', $to)->first();

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $fromStatus->id,
    ]);

    $result = $this->posService->updateOrderStatus($order, $toStatus->id);

    expect($result->processing_status_id)->toBe($toStatus->id);
})->with([
    ['Pending', 'Washing'],
    ['Washing', 'Drying'],
    ['Drying', 'Ready Area'],
    ['Ready Area', 'Delivered'],
]);

it('allows cancellation from non-terminal states', function (string $from) {
    $fromStatus = ProcessingStatus::where('status_name', $from)->first();
    $cancelledStatus = ProcessingStatus::where('status_name', 'Cancelled')->first();

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $fromStatus->id,
    ]);

    $result = $this->posService->updateOrderStatus($order, $cancelledStatus->id);

    expect($result->processing_status_id)->toBe($cancelledStatus->id);
})->with([
    'Pending',
    'Washing',
    'Drying',
]);

it('rejects invalid transitions', function (string $from, string $to) {
    $fromStatus = ProcessingStatus::where('status_name', $from)->first();
    $toStatus = ProcessingStatus::where('status_name', $to)->first();

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $fromStatus->id,
    ]);

    $this->posService->updateOrderStatus($order, $toStatus->id);
})->with([
    ['Delivered', 'Pending'],
    ['Cancelled', 'Pending'],
    ['Pending', 'Ready Area'],
    ['Pending', 'Delivered'],
])->throws(ValidationException::class);

it('allows forced transition for system operations', function () {
    $pendingStatus = ProcessingStatus::where('status_name', 'Pending')->first();
    $deliveredStatus = ProcessingStatus::where('status_name', 'Delivered')->first();

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'processing_status_id' => $pendingStatus->id,
    ]);

    // System-initiated force (e.g., payment completion)
    $result = $this->posService->updateOrderStatus($order, $deliveredStatus->id, force: true);

    expect($result->processing_status_id)->toBe($deliveredStatus->id);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Services/PosStatusTransitionTest.php`
Expected: "rejects invalid transitions" FAIL (no validation exists)

- [ ] **Step 4: Add transition validation to updateOrderStatus**

In `app/Services/PosService.php`, replace the `updateOrderStatus` method (lines 303-333):

```php
    /**
     * Valid processing status transitions.
     * Terminal states (Delivered, Cancelled) have empty arrays — no transitions allowed.
     */
    protected static array $validTransitions = [
        'Pending' => ['Washing', 'Cancelled'],
        'Washing' => ['Drying', 'Pending', 'Cancelled'],
        'Drying' => ['Ready Area', 'Washing', 'Cancelled'],
        'Ready Area' => ['Delivered', 'Drying'],
        'Delivered' => [],
        'Cancelled' => [],
    ];

    /**
     * Update order processing status.
     *
     * @param  bool  $force  Bypass transition validation (for system-initiated changes like payment completion)
     */
    public function updateOrderStatus(Order $order, int $newStatusId, bool $force = false): Order
    {
        $oldStatusId = $order->processing_status_id;

        // Validate transition unless forced by system
        if (! $force) {
            $oldStatus = ProcessingStatus::find($oldStatusId);
            $newStatus = ProcessingStatus::find($newStatusId);

            if ($oldStatus && $newStatus) {
                $allowed = static::$validTransitions[$oldStatus->status_name] ?? [];
                if (! in_array($newStatus->status_name, $allowed)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'status' => ["Cannot transition from '{$oldStatus->status_name}' to '{$newStatus->status_name}'."],
                    ]);
                }
            }
        }

        $order->update([
            'processing_status_id' => $newStatusId,
        ]);

        // If moving to Ready, set actual_ready_date
        if ($newStatusId === ProcessingStatus::idFor(ProcessingStatusEnum::Ready)) {
            $order->update(['actual_ready_date' => now()]);
        }

        // Log status change
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status_type' => OrderStatusTypeEnum::Processing,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'changed_by_employee_id' => auth()->id(),
            'remarks' => 'Status updated via POS',
            'changed_at' => now(),
        ]);

        $freshOrder = $order->fresh(['customer', 'orderItems', 'processingStatus']);

        // Send notification to order creator
        $this->dispatchStatusNotification($freshOrder, $newStatusId);

        return $freshOrder;
    }
```

- [ ] **Step 5: Update processPayment to use force: true**

In `app/Services/PosService.php`, in the `processPayment` method, update the line that calls `updateOrderStatus` (around original line 389):

Replace:
```php
                $this->updateOrderStatus($order, ProcessingStatus::idFor(ProcessingStatusEnum::Delivered));
```

with:
```php
                $this->updateOrderStatus($order, ProcessingStatus::idFor(ProcessingStatusEnum::Delivered), force: true);
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Services/PosStatusTransitionTest.php`
Expected: All PASS

- [ ] **Step 7: Run full test suite**

Run: `php artisan test --compact`
Expected: All PASS (existing tests should not break since `force` defaults to `false`)

- [ ] **Step 8: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Services/PosService.php tests/Feature/Services/PosStatusTransitionTest.php
git commit -m "fix(data): add status transition validation with force bypass for system operations"
```

---

### Task 7: Fix XSS in Events.vue

**Files:**
- Modify: `resources/metronic/components/customers/cards/events-and-logs/Events.vue:38,57-58`

Note: This component currently uses hardcoded static data, not database data. The fix adds DOMPurify as a safety measure in case the component is later connected to real data. DOMPurify is already a project dependency (used in `DatatableHtml.vue`).

- [ ] **Step 1: Add DOMPurify import and sanitize method**

In `resources/metronic/components/customers/cards/events-and-logs/Events.vue`, replace the script section (lines 57-124):

```vue
<script lang="ts">
import { defineComponent, ref } from "vue";
import DOMPurify from "dompurify";

export default defineComponent({
  name: "events-card",
  props: {
    cardClasses: String,
  },
  components: {},
  setup() {
    const events = ref([
      {
        event:
          'Invoice <a href="#" class="fw-bold text-gray-900 text-hover-primary me-1">#LOP-45640</a> has been <span class="badge badge-light-danger">Declined</span>',
        date: "20 Dec 2021, 5:30 pm",
      },
      {
        event:
          'Invoice <a href="#" class="fw-bold text-gray-900 text-hover-primary me-1">#DER-45645</a> status has changed from <span class="badge badge-light-info me-1">In Progress</span> to <span class="badge badge-light-primary">In Transit</span>',
        date: "24 Jun 2021, 5:20 pm",
      },
      {
        event:
          '<a href="#" class="text-gray-600 text-hover-primary me-1">Melody Macy</a> has made payment to <a href="#" class="fw-bold text-gray-900 text-hover-primary">#XRS-45670</a>',
        date: "05 May 2021, 11:05 am",
      },
      {
        event:
          'Invoice <a href="#" class="fw-bold text-gray-900 text-hover-primary me-1">#KIO-45656</a> status has changed from <span class="badge badge-light-succees me-1">In Transit</span> to <span class="badge badge-light-success">Approved</span>',
        date: "20 Dec 2021, 6:43 am",
      },
      {
        event:
          '<a href="#" class="text-gray-600 text-hover-primary me-1">Max Smith</a> has made payment to <a href="#" class="fw-bold text-gray-900 text-hover-primary">#XRS-45670</a>',
        date: "10 Nov 2021, 9:23 pm",
      },
      {
        event:
          'Invoice <a href="#" class="fw-bold text-gray-900 text-hover-primary me-1">#SEP-45656</a> status has changed from <span class="badge badge-light-warning me-1">Pending</span> to <span class="badge badge-light-info">In Progress</span>',
        date: "22 Sep 2021, 5:30 pm",
      },
      {
        event:
          '<a href="#" class="text-gray-600 text-hover-primary me-1">Emma Smith</a> has made payment to <a href="#" class="fw-bold text-gray-900 text-hover-primary">#SDK-45670</a>',
        date: "25 Jul 2021, 8:43 pm",
      },
      {
        event:
          '<a href="#" class="text-gray-600 text-hover-primary me-1">Melody Macy</a> has made payment to <a href="#" class="fw-bold text-gray-900 text-hover-primary">#XRS-45670</a>',
        date: "05 May 2021, 2:40 pm",
      },
      {
        event:
          '<a href="#" class="text-gray-600 text-hover-primary me-1">Emma Smith</a> has made payment to <a href="#" class="fw-bold text-gray-900 text-hover-primary">#OLP-45690</a>',
        date: "25 Oct 2021, 10:30 am",
      },
      {
        event:
          'Invoice <a href="#" class="fw-bold text-gray-900 text-hover-primary me-1">#WER-45670</a> is <span class="badge badge-light-info">In Progress</span>',
        date: "10 Mar 2021, 9:23 pm",
      },
    ]);

    const sanitize = (html: string): string => {
      return DOMPurify.sanitize(html);
    };

    return {
      events,
      sanitize,
    };
  },
});
</script>
```

- [ ] **Step 2: Update template to use sanitize**

In the template section, replace line 38:

```html
              <td class="min-w-400px" v-html="event.event"></td>
```

with:

```html
              <td class="min-w-400px" v-html="sanitize(event.event)"></td>
```

- [ ] **Step 3: Verify build succeeds**

Run: `npm run build`
Expected: Build succeeds without errors

- [ ] **Step 4: Commit**

```bash
git add resources/metronic/components/customers/cards/events-and-logs/Events.vue
git commit -m "fix(security): sanitize v-html output with DOMPurify in Events.vue"
```

---

### Task 8: Add Monetary Validation Bounds to StoreOrderRequest

**Files:**
- Modify: `app/Http/Requests/StoreOrderRequest.php:35,37`

- [ ] **Step 1: Add max constraints**

In `app/Http/Requests/StoreOrderRequest.php`, replace lines 35 and 37:

Replace:
```php
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
```
with:
```php
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
```

Replace:
```php
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
```
with:
```php
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --compact`
Expected: All PASS

- [ ] **Step 3: Commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Requests/StoreOrderRequest.php
git commit -m "fix(data): add max bounds for discount_amount and tip_amount validation"
```

---

### Task 9: Soft-Delete Guard & Loyalty Transaction Safety

**Files:**
- Modify: `app/Services/LoyaltyService.php:42-81,116-133`
- Create: `tests/Feature/Services/LoyaltyServiceTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Services/LoyaltyServiceTest`

- [ ] **Step 2: Write failing tests**

Replace the contents of `tests/Feature/Services/LoyaltyServiceTest.php` with:

```php
<?php

use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\LoyaltyService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->loyaltyService = app(LoyaltyService::class);
});

it('does not award points to soft-deleted customer', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_tier' => 'bronze',
        'loyalty_points' => 0,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
        'total_amount' => 100.00,
        'tip_amount' => 0,
    ]);

    // Soft-delete the customer
    $customer->delete();

    $result = $this->loyaltyService->awardPointsForOrder($order->fresh());

    expect($result)->toBeNull();
});

it('awards points to active customer', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_tier' => 'bronze',
        'loyalty_points' => 0,
        'total_orders_count' => 0,
        'total_spent' => 0,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $customer->id,
        'total_amount' => 100.00,
        'tip_amount' => 0,
    ]);

    $transaction = $this->loyaltyService->awardPointsForOrder($order);

    expect($transaction)->not->toBeNull();
    expect($transaction->points)->toBeGreaterThan(0);
    expect($customer->fresh()->loyalty_points)->toBeGreaterThan(0);
});

it('adds bonus points within a transaction', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'loyalty_tier' => 'bronze',
        'loyalty_points' => 100,
    ]);

    $transaction = $this->loyaltyService->addBonusPoints($customer, 50, 'Birthday bonus');

    expect($transaction->points)->toBe(50);
    expect($transaction->balance_after)->toBe(150);
    expect($customer->fresh()->loyalty_points)->toBe(150);
});
```

- [ ] **Step 3: Run tests to verify the soft-delete test fails**

Run: `php artisan test --compact tests/Feature/Services/LoyaltyServiceTest.php`
Expected: "does not award points to soft-deleted customer" FAILS (no trashed check exists)

- [ ] **Step 4: Add soft-delete guard to awardPointsForOrder**

In `app/Services/LoyaltyService.php`, replace lines 44-47:

```php
        $customer = $order->customer;
        if (! $customer) {
            return null;
        }
```

with:

```php
        $customer = $order->customer;
        if (! $customer || $customer->trashed()) {
            return null;
        }
```

- [ ] **Step 5: Wrap addBonusPoints in a transaction with lock**

In `app/Services/LoyaltyService.php`, replace the `addBonusPoints` method (lines 116-133):

```php
    public function addBonusPoints(Customer $customer, int $points, string $reason): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customer, $points, $reason) {
            // Lock customer row to prevent concurrent balance modifications
            $customer = Customer::where('id', $customer->id)->lockForUpdate()->first();

            $newBalance = $customer->loyalty_points + $points;

            $transaction = LoyaltyTransaction::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'type' => 'bonus',
                'points' => $points,
                'balance_after' => $newBalance,
                'description' => $reason,
            ]);

            $customer->update(['loyalty_points' => $newBalance]);
            $this->updateTier($customer);

            return $transaction;
        });
    }
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Services/LoyaltyServiceTest.php`
Expected: All PASS

- [ ] **Step 7: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Services/LoyaltyService.php tests/Feature/Services/LoyaltyServiceTest.php
git commit -m "fix(data): guard against soft-deleted customers and wrap addBonusPoints in transaction"
```

---

### Task 10: CORS Restriction & Rate Limiting

**Files:**
- Modify: `config/cors.php:20`
- Modify: `app/Providers/AppServiceProvider.php:61-66`
- Modify: `routes/api.php:186-190`

- [ ] **Step 1: Restrict CORS methods**

In `config/cors.php`, replace line 20:

```php
    'allowed_methods' => ['*'],
```

with:

```php
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
```

- [ ] **Step 2: Add write and export rate limiters**

In `app/Providers/AppServiceProvider.php`, replace the `configureRateLimiting` method (lines 61-66):

```php
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('writes', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('exports', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
```

- [ ] **Step 3: Apply throttle middleware to export routes**

In `routes/api.php`, replace the exports route group (lines 187-190):

```php
    // Exports (Excel/PDF - all queued)
    Route::prefix('exports')->name('exports.')->middleware('throttle:exports')->group(function () {
        Route::post('{module}/{format}', 'ExportApiController@export')->name('queue');
        Route::get('download/{filename}', 'ExportApiController@download')->name('download-file');
    });
```

- [ ] **Step 4: Run existing tests**

Run: `php artisan test --compact`
Expected: All PASS

- [ ] **Step 5: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add config/cors.php app/Providers/AppServiceProvider.php routes/api.php
git commit -m "fix(security): restrict CORS methods and add rate limiting for writes and exports"
```

---

### Task 11: Add Missing Database Indexes

**Files:**
- Create: migration (auto-generated filename)

- [ ] **Step 1: Create migration**

Run: `php artisan make:migration add_missing_performance_indexes --no-interaction`

- [ ] **Step 2: Write the migration**

Replace the migration contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // service_prices: individual indexes for JOIN lookups
        Schema::table('service_prices', function (Blueprint $table) {
            $table->index('service_id', 'sp_service_id_idx');
            $table->index('item_id', 'sp_item_id_idx');
        });

        // order_items: tenant-scoped item and service indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['tenant_id', 'item_id'], 'oi_tenant_item_idx');
            $table->index(['tenant_id', 'service_id'], 'oi_tenant_service_idx');
        });

        // payments: tenant and customer history indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('tenant_id', 'p_tenant_idx');
            $table->index(['customer_id', 'payment_date'], 'p_customer_date_idx');
        });

        // orders: POS Kanban board composite index
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['tenant_id', 'processing_status_id', 'order_date'], 'o_tenant_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropIndex('sp_service_id_idx');
            $table->dropIndex('sp_item_id_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('oi_tenant_item_idx');
            $table->dropIndex('oi_tenant_service_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('p_tenant_idx');
            $table->dropIndex('p_customer_date_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('o_tenant_status_date_idx');
        });
    }
};
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration runs successfully

- [ ] **Step 4: Run existing tests**

Run: `php artisan test --compact`
Expected: All PASS

- [ ] **Step 5: Commit**

```bash
git add database/migrations/
git commit -m "perf: add missing indexes for service_prices, order_items, payments, orders"
```

---

## Final Verification

After all tasks are complete:

- [ ] **Run full test suite:** `php artisan test --compact`
- [ ] **Run pint:** `vendor/bin/pint --dirty`
- [ ] **Verify build:** `npm run build`
- [ ] **Verify migration status:** `php artisan migrate:status`
