<?php

use App\Models\Customer;
use App\Models\Order;
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
    $this->customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);

    Gate::define('report_access', fn () => true);
});

test('can get daily report', function () {
    Order::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'order_date' => now(),
        'total_amount' => 100.00,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/reports/daily?date='.now()->format('Y-m-d'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'date',
                'total_orders',
                'total_items',
                'total_revenue',
                'total_collected',
                'pending_amount',
                'urgent_orders',
                'new_customers',
            ],
        ]);
});

test('can get weekly report', function () {
    Order::factory()->count(2)->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'order_date' => now(),
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/reports/weekly?start_date='.now()->startOfWeek()->format('Y-m-d'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'period',
                'start_date',
                'end_date',
                'total_orders',
                'total_revenue',
            ],
        ]);
});

test('can get monthly report', function () {
    Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'order_date' => now(),
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson('/api/v1/reports/monthly?month='.now()->format('Y-m'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'period',
                'start_date',
                'end_date',
                'total_orders',
                'total_revenue',
            ],
        ]);
});

test('can get revenue trend', function () {
    $startDate = now()->subDays(7)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'order_date' => now()->subDays(3),
        'total_amount' => 500.00,
    ]);

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/reports/revenue-trend?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
});

test('can get top services', function () {
    $startDate = now()->subDays(30)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/reports/top-services?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
});

test('can get top customers', function () {
    $startDate = now()->subDays(30)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/reports/top-customers?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
});

test('can get payment methods breakdown', function () {
    $startDate = now()->subDays(30)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/reports/payment-methods?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
});

test('can get status distribution', function () {
    $startDate = now()->subDays(30)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $response = $this->actingAs($this->user, 'admin')
        ->getJson("/api/v1/reports/status-distribution?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
});

test('unauthorized user cannot access reports', function () {
    $response = $this->getJson('/api/v1/reports/daily');

    $response->assertUnauthorized();
});
