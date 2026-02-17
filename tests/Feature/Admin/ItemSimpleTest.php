<?php

use App\Models\Item;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create(['name' => 'Test Tenant', 'domain' => 'test.test', 'active' => true]);
    app(TenantService::class)->setTenant($this->tenant);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    // Allow item permissions for the test
    Gate::define('item_access', fn () => true);
    Gate::define('item_create', fn () => true);
    Gate::define('item_show', fn () => true);
    Gate::define('item_edit', fn () => true);
    Gate::define('item_delete', fn () => true);
});

test('can create an item with a single price', function () {
    $data = [
        'name' => 'Laundry Item',
        'description' => 'Test description',
        'price' => 10.50,
        'display_order' => 1,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->user, 'admin')
        ->postJson('/api/v1/items', $data);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'Laundry Item',
            'price' => '10.50',
        ]);

    $this->assertDatabaseHas('items', [
        'name' => 'Laundry Item',
        'price' => 10.50,
        'tenant_id' => $this->tenant->id,
    ]);
});

test('can update an item price', function () {
    $item = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 5.00,
    ]);

    $data = [
        'name' => 'Updated Item',
        'price' => 15.75,
    ];

    $response = $this->actingAs($this->user, 'admin')
        ->putJson("/api/v1/items/{$item->id}", $data);

    $response->assertStatus(202)
        ->assertJsonFragment([
            'price' => '15.75',
        ]);

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'price' => 15.75,
    ]);
});

test('item resource does not contain multi-price data', function () {
    $item = Item::factory()->create([
        'tenant_id' => $this->tenant->id,
        'price' => 20.00,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/items/{$item->id}");

    $response->assertStatus(200)
        ->assertJsonMissing(['item_prices']);
});
