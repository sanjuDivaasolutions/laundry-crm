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
