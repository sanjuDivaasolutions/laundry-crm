<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SalesOrderStoreRequest;
use App\Http\Requests\API\SalesOrderUpdateRequest;
use App\Http\Resources\API\SalesOrderResource;
use App\Models\SalesOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): ResourceCollection
    {
        $orders = SalesOrder::with(['buyer', 'agent', 'user', 'items.product'])
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('display_name', 'like', "%{$search}%");
                    });
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->get('status'));
            })
            ->when($request->has('buyer_id'), function ($query) use ($request) {
                $query->where('buyer_id', $request->get('buyer_id'));
            })
            ->when($request->has('agent_id'), function ($query) use ($request) {
                $query->where('agent_id', $request->get('agent_id'));
            })
            ->advancedFilter();

        return SalesOrderResource::collection($orders);
    }

    public function store(SalesOrderStoreRequest $request): JsonResource
    {
        $order = $this->orderService->createSalesOrder($request->validated());

        return new SalesOrderResource($order->load(['buyer', 'agent', 'user', 'items.product']));
    }

    public function show(SalesOrder $order): JsonResource
    {
        $order->load(['buyer', 'agent', 'user', 'items.product', 'commissions']);

        return new SalesOrderResource($order);
    }

    public function update(SalesOrderUpdateRequest $request, SalesOrder $order): JsonResource
    {
        $updatedOrder = $this->orderService->updateSalesOrder($order, $request->validated());

        return new SalesOrderResource($updatedOrder->load(['buyer', 'agent', 'user', 'items.product']));
    }

    public function destroy(SalesOrder $order): JsonResponse
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function convertToInvoice(Request $request, SalesOrder $order): JsonResource
    {
        $request->validate([
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->orderService->convertToInvoice($order, $request->all());

        return new SalesOrderResource($invoice->load(['buyer', 'agent', 'user', 'items.product']));
    }

    public function getOrderStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $companyId = $request->get('company_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($companyId) {
            $company = \App\Models\Company::findOrFail($companyId);
            $statistics = $this->orderService->getOrderStatistics($company, $startDate, $endDate);
        } else {
            // Get statistics for all companies
            $statistics = [
                'total_orders' => SalesOrder::count(),
                'total_amount' => SalesOrder::sum('grand_total'),
                'pending_orders' => SalesOrder::where('status', 'pending')->count(),
                'confirmed_orders' => SalesOrder::where('status', 'confirmed')->count(),
                'converted_orders' => SalesOrder::where('status', 'converted')->count(),
                'cancelled_orders' => SalesOrder::where('status', 'cancelled')->count(),
                'average_order_value' => SalesOrder::avg('grand_total') ?? 0,
            ];
        }

        return response()->json($statistics);
    }

    public function updateStatus(Request $request, SalesOrder $order): JsonResource
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,converted,cancelled',
            'notes' => 'nullable|string',
        ]);

        $order->update([
            'status' => $request->get('status'),
            'notes' => $request->get('notes', $order->notes),
        ]);

        return new SalesOrderResource($order->load(['buyer', 'agent', 'user', 'items.product']));
    }
}