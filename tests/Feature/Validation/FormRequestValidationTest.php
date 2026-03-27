<?php

use App\Models\Customer;
use App\Models\Item;
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

    Gate::define('order_create', fn () => true);
    Gate::define('order_access', fn () => true);
    Gate::define('order_show', fn () => true);
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
