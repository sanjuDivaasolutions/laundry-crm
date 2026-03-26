<?php

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
