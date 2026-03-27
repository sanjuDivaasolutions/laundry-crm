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
