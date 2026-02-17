<?php

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create(['name' => 'Test Tenant', 'domain' => 'test.test', 'active' => true]);
    app(TenantService::class)->setTenant($this->tenant);

    ProcessingStatus::create(['status_name' => 'Pending', 'display_order' => 1, 'is_active' => true]);
    OrderStatus::create(['status_name' => 'Open', 'display_order' => 1, 'is_active' => true]);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('activity is logged when order is created', function () {
    $this->actingAs($this->user, 'admin');

    $order = Order::factory()->create(['tenant_id' => $this->tenant->id]);

    $activity = Activity::where('subject_type', Order::class)
        ->where('subject_id', $order->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->log_name)->toBe('order');
    expect($activity->description)->toContain('created');
    expect($activity->tenant_id)->toBe($this->tenant->id);
});

test('activity is logged when order is updated', function () {
    $this->actingAs($this->user, 'admin');

    $order = Order::factory()->create(['tenant_id' => $this->tenant->id]);

    $order->update(['urgent' => true]);

    $activity = Activity::where('subject_type', Order::class)
        ->where('subject_id', $order->id)
        ->where('event', 'updated')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->log_name)->toBe('order');
    expect($activity->properties['old'])->toHaveKey('urgent');
    expect($activity->properties['attributes'])->toHaveKey('urgent');
});

test('activity is logged when customer is created', function () {
    $this->actingAs($this->user, 'admin');

    $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $activity = Activity::where('subject_type', Customer::class)
        ->where('subject_id', $customer->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->log_name)->toBe('customer');
    expect($activity->tenant_id)->toBe($this->tenant->id);
});

test('activity is logged when item is updated', function () {
    $this->actingAs($this->user, 'admin');

    $item = Item::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Shirt']);

    $item->update(['name' => 'Polo Shirt']);

    $activity = Activity::where('subject_type', Item::class)
        ->where('subject_id', $item->id)
        ->where('event', 'updated')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->log_name)->toBe('item');
    expect($activity->properties['old']['name'])->toBe('Shirt');
    expect($activity->properties['attributes']['name'])->toBe('Polo Shirt');
});

test('activity is logged when service is deleted', function () {
    $this->actingAs($this->user, 'admin');

    $service = Service::factory()->create(['tenant_id' => $this->tenant->id]);
    $serviceId = $service->id;

    $service->delete();

    $activity = Activity::where('subject_type', Service::class)
        ->where('subject_id', $serviceId)
        ->where('event', 'deleted')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->log_name)->toBe('service');
});

test('activity logs are tenant scoped', function () {
    $tenantB = Tenant::create(['name' => 'Tenant B', 'domain' => 'b.test', 'active' => true]);

    // Create activity in tenant A context
    $this->actingAs($this->user, 'admin');
    Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    // Switch to tenant B
    app(TenantService::class)->setTenant($tenantB);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
    $this->actingAs($userB, 'admin');
    Customer::factory()->create(['tenant_id' => $tenantB->id]);

    // Tenant B should only see their own activities
    $activities = Activity::all();
    expect($activities->pluck('tenant_id')->unique()->toArray())->toBe([$tenantB->id]);

    // Switch back to tenant A
    app(TenantService::class)->setTenant($this->tenant);
    $activitiesA = Activity::all();
    expect($activitiesA->pluck('tenant_id')->unique()->toArray())->toBe([$this->tenant->id]);
});

test('activity log causer is the authenticated user', function () {
    $this->actingAs($this->user, 'admin');

    Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    $activity = Activity::where('log_name', 'customer')
        ->where('event', 'created')
        ->first();

    expect($activity->causer_id)->toBe($this->user->id);
    expect($activity->causer_type)->toBe(User::class);
});

test('activity log API returns paginated results', function () {
    $this->actingAs($this->user, 'admin');
    Gate::define('activity_log_access', fn () => true);

    // Create some activities
    Customer::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->getJson('/api/v1/activity-logs');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'log_name',
                'description',
                'subject_type',
                'subject_id',
                'causer_id',
                'properties',
                'created_at',
            ],
        ],
    ]);
});

test('activity log API filters by log name', function () {
    $this->actingAs($this->user, 'admin');
    Gate::define('activity_log_access', fn () => true);

    Customer::factory()->create(['tenant_id' => $this->tenant->id]);
    Item::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->getJson('/api/v1/activity-logs?log_name=customer');

    $response->assertSuccessful();
    $data = $response->json('data');
    foreach ($data as $activity) {
        expect($activity['log_name'])->toBe('customer');
    }
});

test('activity log API requires authorization', function () {
    $this->actingAs($this->user, 'admin');
    Gate::define('activity_log_access', fn () => false);

    $response = $this->getJson('/api/v1/activity-logs');

    $response->assertForbidden();
});
