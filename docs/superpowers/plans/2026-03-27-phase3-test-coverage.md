# Phase 3: Test Coverage Audit Fixes

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 5 test suites covering PosService integration, controller CRUD (Customer, Service, DeliverySchedule), and form request validation.

**Architecture:** Pure test code — no production changes. Each task creates one test file with its own commit. All tests follow the established Pest pattern: `RefreshDatabase`, tenant setup via `TenantService`, gate mocking, `actingAs($user, 'admin')`.

**Tech Stack:** Pest v3, Laravel 11, PHP 8.2

---

## File Map

| File | Task | Tests |
|------|------|-------|
| `tests/Feature/Services/PosServiceIntegrationTest.php` | 1 | End-to-end: createQuickOrder → processPayment → loyalty |
| `tests/Feature/Controllers/Api/CustomerControllerTest.php` | 2 | CRUD + auth + validation (10+ tests) |
| `tests/Feature/Controllers/Api/ServiceControllerTest.php` | 3 | CRUD + auth + validation (10+ tests) |
| `tests/Feature/Controllers/Api/DeliveryScheduleControllerTest.php` | 4 | All 6 endpoints + auth + validation |
| `tests/Feature/Validation/FormRequestValidationTest.php` | 5 | StoreOrder, StoreCustomer, StoreService validation |

---

### Task 1: PosService Integration Test

**Files:**
- Create: `tests/Feature/Services/PosServiceIntegrationTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Services/PosServiceIntegrationTest`

- [ ] **Step 2: Write the integration tests**

Replace the contents of `tests/Feature/Services/PosServiceIntegrationTest.php` with:

```php
<?php

use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PosService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

    // Create items, service, and service prices for order creation
    $this->service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'pricing_type' => 'piece',
    ]);

    $this->item1 = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 25.00,
    ]);

    $this->item2 = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 15.00,
    ]);

    ServicePrice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'item_id' => $this->item1->id,
        'service_id' => $this->service->id,
        'price' => 30.00,
    ]);

    ServicePrice::factory()->create([
        'tenant_id' => $this->tenant->id,
        'item_id' => $this->item2->id,
        'service_id' => $this->service->id,
        'price' => 20.00,
    ]);

    $this->posService = app(PosService::class);
});

it('creates a quick order with correct totals and customer', function () {
    $data = [
        'customer_name' => 'John Doe',
        'customer_phone' => '555-1234',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item1->id, 'quantity' => 2],
            ['item_id' => $this->item2->id, 'quantity' => 1],
        ],
    ];

    $order = $this->posService->createQuickOrder($data);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->order_number)->toStartWith('ORD');
    expect((float) $order->total_amount)->toBe(80.00); // (30*2) + (20*1)
    expect((float) $order->balance_amount)->toBe(80.00);
    expect($order->payment_status)->toBe(PaymentStatusEnum::Unpaid);
    expect($order->processingStatus->status_name)->toBe(ProcessingStatusEnum::Pending->value);

    // Customer was created
    $customer = Customer::where('phone', '555-1234')->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('John Doe');
    expect($order->customer_id)->toBe($customer->id);

    // Order items were created
    expect($order->orderItems)->toHaveCount(2);
});

it('processes payment and completes the full order lifecycle', function () {
    // Step 1: Create order
    $order = $this->posService->createQuickOrder([
        'customer_name' => 'Jane Smith',
        'customer_phone' => '555-5678',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item1->id, 'quantity' => 1],
        ],
    ]);

    expect((float) $order->total_amount)->toBe(30.00);

    // Step 2: Pay in full
    $result = $this->posService->processPayment($order, [
        'amount' => 30.00,
        'payment_method' => 'cash',
    ]);

    $paidOrder = $result['order'];
    $payment = $result['payment'];

    // Order is fully paid and delivered
    expect($paidOrder->payment_status)->toBe(PaymentStatusEnum::Paid);
    expect((float) $paidOrder->balance_amount)->toBe(0.00);
    expect($paidOrder->processingStatus->status_name)->toBe(ProcessingStatusEnum::Delivered->value);
    expect($paidOrder->closed_at)->not->toBeNull();

    // Payment record created
    expect($payment->payment_number)->toStartWith('PAY');
    expect((float) $payment->amount)->toBe(30.00);

    // Loyalty points awarded to customer
    $customer = Customer::where('phone', '555-5678')->first();
    expect($customer->loyalty_points)->toBeGreaterThan(0);
    expect($customer->total_orders_count)->toBe(1);
    expect((float) $customer->total_spent)->toBe(30.00);
});

it('finds existing customer by phone on subsequent orders', function () {
    // First order creates customer
    $this->posService->createQuickOrder([
        'customer_name' => 'Repeat Customer',
        'customer_phone' => '555-9999',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item1->id, 'quantity' => 1],
        ],
    ]);

    $customerCount = Customer::where('phone', '555-9999')->count();
    expect($customerCount)->toBe(1);

    // Second order reuses same customer
    $order2 = $this->posService->createQuickOrder([
        'customer_name' => 'Repeat Customer',
        'customer_phone' => '555-9999',
        'service_id' => $this->service->id,
        'items' => [
            ['item_id' => $this->item2->id, 'quantity' => 1],
        ],
    ]);

    // Still only 1 customer with that phone
    $customerCount = Customer::where('phone', '555-9999')->count();
    expect($customerCount)->toBe(1);

    // Both orders belong to the same customer
    $customer = Customer::where('phone', '555-9999')->first();
    expect($order2->customer_id)->toBe($customer->id);
});
```

- [ ] **Step 3: Run the tests**

Run: `php artisan test --compact tests/Feature/Services/PosServiceIntegrationTest.php`
Expected: All PASS

- [ ] **Step 4: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add tests/Feature/Services/PosServiceIntegrationTest.php
git commit -m "test: add PosService integration tests for order lifecycle"
```

---

### Task 2: CustomerApiController Tests

**Files:**
- Create: `tests/Feature/Controllers/Api/CustomerControllerTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Controllers/Api/CustomerControllerTest`

- [ ] **Step 2: Write the CRUD and authorization tests**

Replace the contents of `tests/Feature/Controllers/Api/CustomerControllerTest.php` with:

```php
<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Gate::define('customer_access', fn () => true);
    Gate::define('customer_create', fn () => true);
    Gate::define('customer_show', fn () => true);
    Gate::define('customer_edit', fn () => true);
    Gate::define('customer_delete', fn () => true);
});

// --- CRUD Happy Paths ---

it('lists customers', function () {
    Customer::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/customers');

    $response->assertSuccessful();
});

it('creates a customer', function () {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/customers', [
            'name' => 'Alice Johnson',
            'phone' => '555-0001',
            'address' => '123 Main St',
            'is_active' => true,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('customers', [
        'name' => 'Alice Johnson',
        'phone' => '555-0001',
        'tenant_id' => $this->tenant->id,
    ]);

    // Auto-generated customer code
    $customer = Customer::where('phone', '555-0001')->first();
    expect($customer->customer_code)->toStartWith('CUST-');
});

it('shows a customer', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/customers/{$customer->id}");

    $response->assertSuccessful();
});

it('returns edit resource for a customer', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/customers/{$customer->id}/edit");

    $response->assertSuccessful()
        ->assertJsonStructure(['data', 'meta']);
});

it('updates a customer', function () {
    $customer = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Old Name',
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/customers/{$customer->id}", [
            'name' => 'New Name',
            'phone' => $customer->phone,
        ]);

    $response->assertStatus(202);
    expect($customer->fresh()->name)->toBe('New Name');
});

it('soft-deletes a customer', function () {
    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->deleteJson("/api/v1/customers/{$customer->id}");

    $response->assertStatus(204);
    expect($customer->fresh()->trashed())->toBeTrue();
});

// --- Authorization ---

it('returns 403 when listing customers without permission', function () {
    Gate::define('customer_access', fn () => false);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/customers');

    $response->assertForbidden();
});

it('returns 403 when creating customer without permission', function () {
    Gate::define('customer_create', fn () => false);

    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/customers', [
            'name' => 'Unauthorized',
            'phone' => '555-0002',
        ]);

    $response->assertForbidden();
});

it('returns 403 when deleting customer without permission', function () {
    Gate::define('customer_delete', fn () => false);

    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->deleteJson("/api/v1/customers/{$customer->id}");

    $response->assertForbidden();
});

// --- Validation ---

it('rejects customer creation with missing required fields', function (array $data, string $errorField) {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/customers', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors($errorField);
})->with([
    'missing name' => [['phone' => '555-0003'], 'name'],
    'missing phone' => [['name' => 'No Phone'], 'phone'],
    'name too long' => [['name' => str_repeat('a', 256), 'phone' => '555-0004'], 'name'],
    'phone too long' => [['name' => 'Test', 'phone' => str_repeat('5', 21)], 'phone'],
]);
```

- [ ] **Step 3: Run the tests**

Run: `php artisan test --compact tests/Feature/Controllers/Api/CustomerControllerTest.php`
Expected: All PASS

- [ ] **Step 4: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add tests/Feature/Controllers/Api/CustomerControllerTest.php
git commit -m "test: add CustomerApiController CRUD and authorization tests"
```

---

### Task 3: ServiceApiController Tests

**Files:**
- Create: `tests/Feature/Controllers/Api/ServiceControllerTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Controllers/Api/ServiceControllerTest`

- [ ] **Step 2: Write the CRUD and authorization tests**

Replace the contents of `tests/Feature/Controllers/Api/ServiceControllerTest.php` with:

```php
<?php

use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Gate::define('service_access', fn () => true);
    Gate::define('service_create', fn () => true);
    Gate::define('service_show', fn () => true);
    Gate::define('service_edit', fn () => true);
    Gate::define('service_delete', fn () => true);
});

// --- CRUD Happy Paths ---

it('lists services', function () {
    Service::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/services');

    $response->assertSuccessful();
});

it('creates a service', function () {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/services', [
            'name' => 'Dry Clean',
            'description' => 'Professional dry cleaning',
            'display_order' => 1,
            'is_active' => true,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('services', [
        'name' => 'Dry Clean',
        'tenant_id' => $this->tenant->id,
    ]);

    // Auto-generated code
    $service = Service::where('name', 'Dry Clean')->first();
    expect($service->code)->toStartWith('SVC-');
});

it('shows a service', function () {
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/services/{$service->id}");

    $response->assertSuccessful();
});

it('returns edit resource for a service', function () {
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/services/{$service->id}/edit");

    $response->assertSuccessful()
        ->assertJsonStructure(['data', 'meta']);
});

it('updates a service', function () {
    $service = Service::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Old Service',
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/services/{$service->id}", [
            'name' => 'Updated Service',
        ]);

    $response->assertStatus(202);
    expect($service->fresh()->name)->toBe('Updated Service');
});

it('soft-deletes a service', function () {
    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->deleteJson("/api/v1/services/{$service->id}");

    $response->assertStatus(204);
    expect($service->fresh()->trashed())->toBeTrue();
});

// --- Authorization ---

it('returns 403 when listing services without permission', function () {
    Gate::define('service_access', fn () => false);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/services');

    $response->assertForbidden();
});

it('returns 403 when creating service without permission', function () {
    Gate::define('service_create', fn () => false);

    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/services', [
            'name' => 'Unauthorized Service',
        ]);

    $response->assertForbidden();
});

it('returns 403 when deleting service without permission', function () {
    Gate::define('service_delete', fn () => false);

    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user, 'admin')
        ->deleteJson("/api/v1/services/{$service->id}");

    $response->assertForbidden();
});

// --- Validation ---

it('rejects service creation with missing name', function () {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/services', [
            'description' => 'No name provided',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('rejects service creation with name too long', function () {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/services', [
            'name' => str_repeat('a', 101),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});
```

- [ ] **Step 3: Run the tests**

Run: `php artisan test --compact tests/Feature/Controllers/Api/ServiceControllerTest.php`
Expected: All PASS

- [ ] **Step 4: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add tests/Feature/Controllers/Api/ServiceControllerTest.php
git commit -m "test: add ServiceApiController CRUD and authorization tests"
```

---

### Task 4: DeliveryScheduleApiController Tests

**Files:**
- Create: `tests/Feature/Controllers/Api/DeliveryScheduleControllerTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Controllers/Api/DeliveryScheduleControllerTest`

- [ ] **Step 2: Write the endpoint tests**

Replace the contents of `tests/Feature/Controllers/Api/DeliveryScheduleControllerTest.php` with:

```php
<?php

use App\Models\Customer;
use App\Models\DeliverySchedule;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
    ]);

    Gate::define('order_access', fn () => true);
    Gate::define('order_create', fn () => true);
    Gate::define('order_edit', fn () => true);
    Gate::define('order_delete', fn () => true);
});

// --- CRUD Happy Paths ---

it('lists delivery schedules', function () {
    DeliverySchedule::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $this->order->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/deliveries');

    $response->assertSuccessful();
});

it('returns today schedules with summary', function () {
    DeliverySchedule::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $this->order->id,
        'customer_id' => $this->customer->id,
        'type' => 'pickup',
        'scheduled_date' => today(),
        'status' => 'pending',
    ]);

    DeliverySchedule::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $this->order->id,
        'customer_id' => $this->customer->id,
        'type' => 'delivery',
        'scheduled_date' => today(),
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/deliveries/today');

    $response->assertSuccessful()
        ->assertJsonPath('data.summary.total', 2)
        ->assertJsonPath('data.summary.pickups', 1)
        ->assertJsonPath('data.summary.deliveries', 1)
        ->assertJsonPath('data.summary.pending', 1)
        ->assertJsonPath('data.summary.completed', 1);
});

it('creates a delivery schedule', function () {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/deliveries', [
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'type' => 'pickup',
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'scheduled_time' => '10:00',
            'address' => '456 Oak Ave',
            'notes' => 'Ring doorbell',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('delivery_schedules', [
        'order_id' => $this->order->id,
        'type' => 'pickup',
        'address' => '456 Oak Ave',
    ]);
});

it('returns edit resource for a delivery schedule', function () {
    $schedule = DeliverySchedule::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $this->order->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/deliveries/{$schedule->id}/edit");

    $response->assertSuccessful();
});

it('updates a delivery schedule and sets completed_at', function () {
    $schedule = DeliverySchedule::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $this->order->id,
        'customer_id' => $this->customer->id,
        'status' => 'pending',
        'completed_at' => null,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/deliveries/{$schedule->id}", [
            'status' => 'completed',
        ]);

    $response->assertSuccessful();
    expect($schedule->fresh()->status)->toBe('completed');
    expect($schedule->fresh()->completed_at)->not->toBeNull();
});

it('deletes a delivery schedule', function () {
    $schedule = DeliverySchedule::factory()->create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $this->order->id,
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->deleteJson("/api/v1/deliveries/{$schedule->id}");

    $response->assertSuccessful();
    expect($schedule->fresh()->trashed())->toBeTrue();
});

// --- Authorization ---

it('returns 403 when listing deliveries without permission', function () {
    Gate::define('order_access', fn () => false);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/deliveries');

    $response->assertForbidden();
});

it('returns 403 when creating delivery without permission', function () {
    Gate::define('order_create', fn () => false);

    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/deliveries', [
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'type' => 'pickup',
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
        ]);

    $response->assertForbidden();
});

// --- Validation ---

it('rejects delivery creation with invalid data', function (array $data, string $errorField) {
    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/deliveries', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors($errorField);
})->with([
    'missing order_id' => [['customer_id' => 1, 'type' => 'pickup', 'scheduled_date' => '2030-01-01'], 'order_id'],
    'missing type' => [fn () => ['order_id' => Order::first()->id, 'customer_id' => Customer::first()->id, 'scheduled_date' => '2030-01-01'], 'type'],
    'invalid type' => [fn () => ['order_id' => Order::first()->id, 'customer_id' => Customer::first()->id, 'type' => 'invalid', 'scheduled_date' => '2030-01-01'], 'type'],
    'past date' => [fn () => ['order_id' => Order::first()->id, 'customer_id' => Customer::first()->id, 'type' => 'pickup', 'scheduled_date' => '2020-01-01'], 'scheduled_date'],
]);
```

- [ ] **Step 3: Run the tests**

Run: `php artisan test --compact tests/Feature/Controllers/Api/DeliveryScheduleControllerTest.php`
Expected: All PASS

- [ ] **Step 4: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add tests/Feature/Controllers/Api/DeliveryScheduleControllerTest.php
git commit -m "test: add DeliveryScheduleApiController endpoint and authorization tests"
```

---

### Task 5: Form Request Validation Tests

**Files:**
- Create: `tests/Feature/Validation/FormRequestValidationTest.php`

- [ ] **Step 1: Create the test file**

Run: `php artisan make:test --pest Validation/FormRequestValidationTest`

- [ ] **Step 2: Write the validation tests**

Replace the contents of `tests/Feature/Validation/FormRequestValidationTest.php` with:

```php
<?php

use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ProcessingStatusSeeder']);
    $this->artisan('db:seed', ['--class' => 'OrderStatusSeeder']);

    $this->tenant = Tenant::factory()->create();
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    // Allow all relevant permissions
    Gate::define('order_create', fn () => true);
    Gate::define('customer_create', fn () => true);
    Gate::define('service_create', fn () => true);
});

// --- StoreOrderRequest ---

describe('StoreOrderRequest', function () {
    it('rejects order with missing required fields', function (array $data, string $errorField) {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($errorField);
    })->with([
        'missing customer_id' => [['order_date' => '2026-03-27', 'promised_date' => '2026-03-29', 'items' => [['item_id' => 1, 'service_id' => 1, 'quantity' => 1, 'unit_price' => 10]]], 'customer_id'],
        'missing order_date' => [fn () => ['customer_id' => Customer::factory()->create(['tenant_id' => Tenant::first()->id])->id, 'promised_date' => '2026-03-29', 'items' => [['item_id' => 1, 'service_id' => 1, 'quantity' => 1, 'unit_price' => 10]]], 'order_date'],
        'missing items' => [fn () => ['customer_id' => Customer::factory()->create(['tenant_id' => Tenant::first()->id])->id, 'order_date' => '2026-03-27', 'promised_date' => '2026-03-29'], 'items'],
    ]);

    it('rejects order with discount exceeding max', function () {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/orders', [
                'customer_id' => $customer->id,
                'order_date' => '2026-03-27',
                'promised_date' => '2026-03-29',
                'discount_type' => 'fixed',
                'discount_amount' => 100000.00,
                'items' => [
                    ['item_id' => $item->id, 'service_id' => $service->id, 'quantity' => 1, 'unit_price' => 10],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('discount_amount');
    });

    it('rejects order with tip exceeding max', function () {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/orders', [
                'customer_id' => $customer->id,
                'order_date' => '2026-03-27',
                'promised_date' => '2026-03-29',
                'tip_amount' => 10000.00,
                'items' => [
                    ['item_id' => $item->id, 'service_id' => $service->id, 'quantity' => 1, 'unit_price' => 10],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('tip_amount');
    });

    it('accepts valid order data', function () {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $item = Item::factory()->create(['tenant_id' => $this->tenant->id]);
        $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);

        Gate::define('order_access', fn () => true);
        Gate::define('order_show', fn () => true);

        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/orders', [
                'customer_id' => $customer->id,
                'order_date' => now()->format('Y-m-d'),
                'promised_date' => now()->addDays(2)->format('Y-m-d'),
                'discount_type' => 'fixed',
                'discount_amount' => 5.00,
                'tax_rate' => 10,
                'items' => [
                    [
                        'item_id' => $item->id,
                        'service_id' => $service->id,
                        'quantity' => 2,
                        'unit_price' => 25.00,
                    ],
                ],
            ]);

        $response->assertStatus(201);
    });
});

// --- StoreCustomerRequest ---

describe('StoreCustomerRequest', function () {
    it('rejects customer with missing required fields', function (array $data, string $errorField) {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/customers', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($errorField);
    })->with([
        'missing name' => [['phone' => '555-0001'], 'name'],
        'missing phone' => [['name' => 'Test Customer'], 'phone'],
    ]);

    it('rejects customer with fields exceeding max length', function (array $data, string $errorField) {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/customers', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($errorField);
    })->with([
        'name too long' => [['name' => str_repeat('a', 256), 'phone' => '555-0001'], 'name'],
        'phone too long' => [['name' => 'Test', 'phone' => str_repeat('5', 21)], 'phone'],
    ]);

    it('accepts valid customer data', function () {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/customers', [
                'name' => 'Valid Customer',
                'phone' => '555-1234',
                'address' => '100 Test Lane',
                'is_active' => true,
            ]);

        $response->assertStatus(201);
    });
});

// --- StoreServiceRequest ---

describe('StoreServiceRequest', function () {
    it('rejects service with missing name', function () {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/services', [
                'description' => 'No name',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    });

    it('rejects service with name exceeding max length', function () {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/services', [
                'name' => str_repeat('a', 101),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    });

    it('accepts valid service data', function () {
        $response = $this->actingAs($this->user, 'admin')
            ->postJson('/api/v1/services', [
                'name' => 'Express Wash',
                'description' => 'Same-day wash service',
                'display_order' => 1,
                'is_active' => true,
            ]);

        $response->assertStatus(201);
    });
});
```

- [ ] **Step 3: Run the tests**

Run: `php artisan test --compact tests/Feature/Validation/FormRequestValidationTest.php`
Expected: All PASS

- [ ] **Step 4: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add tests/Feature/Validation/FormRequestValidationTest.php
git commit -m "test: add form request validation tests for Order, Customer, and Service"
```

---

## Final Verification

After all tasks are complete:

- [ ] **Run full test suite:** `php artisan test --compact`
- [ ] **Run pint:** `vendor/bin/pint --dirty`
