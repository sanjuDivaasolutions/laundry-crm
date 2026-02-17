<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DeliveryScheduleApiController extends Controller
{
    /**
     * List delivery schedules with optional date filter.
     *
     * GET /api/v1/deliveries?date=2026-02-13&type=pickup
     */
    public function index(Request $request): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        $query = DeliverySchedule::with(['order', 'customer'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time');

        if ($date = $request->query('date')) {
            $query->forDate($date);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $schedules = $query->paginate(20);

        return $this->success($schedules);
    }

    /**
     * Get today's delivery schedule.
     *
     * GET /api/v1/deliveries/today
     */
    public function today(): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        $schedules = DeliverySchedule::with(['order', 'customer'])
            ->today()
            ->orderBy('scheduled_time')
            ->get();

        $summary = [
            'total' => $schedules->count(),
            'pickups' => $schedules->where('type', 'pickup')->count(),
            'deliveries' => $schedules->where('type', 'delivery')->count(),
            'pending' => $schedules->where('status', 'pending')->count(),
            'completed' => $schedules->where('status', 'completed')->count(),
        ];

        return $this->success([
            'schedules' => $schedules,
            'summary' => $summary,
        ]);
    }

    /**
     * Get a single delivery schedule for editing.
     *
     * GET /api/v1/deliveries/{delivery}/edit
     */
    public function edit(DeliverySchedule $delivery): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        return $this->success($delivery->load(['order', 'customer']));
    }

    /**
     * Schedule a pickup or delivery.
     *
     * POST /api/v1/deliveries
     */
    public function store(Request $request): JsonResponse
    {
        abort_if(Gate::denies('order_create'), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'type' => ['required', 'string', 'in:pickup,delivery'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'assigned_to_employee_id' => ['nullable', 'integer'],
        ]);

        $schedule = DeliverySchedule::create($validated);

        return $this->success(
            $schedule->load(['order', 'customer']),
            'Delivery scheduled successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Update a delivery schedule.
     *
     * PUT /api/v1/deliveries/{deliverySchedule}
     */
    public function update(Request $request, DeliverySchedule $delivery): JsonResponse
    {
        abort_if(Gate::denies('order_edit'), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'scheduled_date' => ['sometimes', 'date'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'assigned_to_employee_id' => ['nullable', 'integer'],
            'status' => ['sometimes', 'string', 'in:pending,confirmed,in_transit,completed,cancelled'],
        ]);

        $delivery->update($validated);

        if (($validated['status'] ?? null) === 'completed') {
            $delivery->update(['completed_at' => now()]);
        }

        return $this->success($delivery->fresh(['order', 'customer']), 'Schedule updated successfully');
    }

    /**
     * Delete a delivery schedule.
     *
     * DELETE /api/v1/deliveries/{deliverySchedule}
     */
    public function destroy(DeliverySchedule $delivery): JsonResponse
    {
        abort_if(Gate::denies('order_delete'), Response::HTTP_FORBIDDEN);

        $delivery->delete();

        return $this->success(null, 'Schedule deleted');
    }
}
