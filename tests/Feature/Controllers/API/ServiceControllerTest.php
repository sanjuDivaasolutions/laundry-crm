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
