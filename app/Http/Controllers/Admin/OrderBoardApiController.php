<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderItemResource;
use App\Http\Resources\Admin\OrderStatusHistoryResource;
use App\Http\Resources\Admin\PaymentResource;
use App\Models\Customer;
use App\Models\Order;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * OrderBoardApiController
 *
 * API endpoints for the POS Kanban board.
 */
class OrderBoardApiController extends Controller
{
    public function __construct(
        protected PosService $posService
    ) {}

    /**
     * Get all Kanban board data.
     *
     * GET /api/v1/pos/board
     */
    public function getBoardData(): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        $data = $this->posService->getKanbanData();

        return $this->success($data);
    }

    /**
     * Get statistics only (for polling).
     *
     * GET /api/v1/pos/statistics
     */
    public function getStatistics(): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        return $this->success($this->posService->getStatistics());
    }

    /**
     * Get items with service prices.
     *
     * GET /api/v1/pos/items
     */
    public function getItems(): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        return $this->success($this->posService->getItemsWithPrices());
    }

    /**
     * Create a quick order.
     *
     * POST /api/v1/pos/orders
     */
    public function store(Request $request): JsonResponse
    {
        abort_if(Gate::denies('order_create'), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.weight' => ['nullable', 'numeric', 'min:0.1'],
            'items.*.weight_unit' => ['nullable', 'string', 'in:lb,kg'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'urgent' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'promised_date' => ['nullable', 'date'],
        ]);

        $order = $this->posService->createQuickOrder($validated);

        return $this->success($this->formatOrderForBoard($order), 'Order created successfully', Response::HTTP_CREATED);
    }

    /**
     * Update order processing status.
     *
     * PUT /api/v1/pos/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        abort_if(Gate::denies('order_edit'), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'processing_status_id' => ['required', 'integer', 'exists:processing_status,id'],
        ]);

        $order = $this->posService->updateOrderStatus($order, $validated['processing_status_id']);

        return $this->success($this->formatOrderForBoard($order), 'Status updated successfully');
    }

    /**
     * Process payment for an order.
     *
     * POST /api/v1/pos/orders/{order}/pay
     */
    public function processPayment(Request $request, Order $order): JsonResponse
    {
        abort_if(Gate::denies('order_edit'), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,card,apple_pay,google_pay,other'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->posService->processPayment($order, $validated);

        return $this->success([
            'payment' => $result['payment'],
            'order' => $this->formatOrderForBoard($result['order']),
        ], 'Payment processed successfully');
    }

    /**
     * Get single order details for payment panel.
     *
     * GET /api/v1/pos/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        abort_if(Gate::denies('order_show'), Response::HTTP_FORBIDDEN);

        $order->load(['customer', 'orderItems', 'processingStatus', 'payments']);

        return $this->success([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
            ],
            'payments' => PaymentResource::collection($order->payments),
            'items' => OrderItemResource::collection($order->orderItems),
            'history' => OrderStatusHistoryResource::collection($order->statusHistories()->oldest('changed_at')->get()),
            'subtotal' => (float) $order->subtotal,
            'discount_amount' => (float) $order->discount_amount,
            'tip_amount' => (float) $order->tip_amount,
            'total_amount' => (float) $order->total_amount,
            'paid_amount' => (float) $order->paid_amount,
            'balance_amount' => (float) $order->balance_amount,
            'payment_status' => $order->payment_status->value,
            'processing_status' => $order->processingStatus->status_name,
            'processing_status_id' => $order->processing_status_id,
            'urgent' => $order->urgent,
            'notes' => $order->notes,
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Search customers by phone number or name.
     *
     * GET /api/v1/pos/customers/search
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        $query = $request->query('q', $request->query('phone', ''));

        if (strlen($query) < 3) {
            return $this->success([]);
        }

        $customers = Customer::where(function ($q) use ($query) {
            $q->where('phone', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%");
        })
            ->limit(5)
            ->get(['id', 'name', 'phone', 'customer_code']);

        return $this->success($customers);
    }

    /**
     * Format order data for the Kanban board.
     */
    protected function formatOrderForBoard(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer->name,
            'customer_phone' => $order->customer->phone,
            'service_name' => $order->orderItems->first()?->service_name ?? '',
            'item_summary' => $this->getItemSummary($order),
            'total_amount' => (float) $order->total_amount,
            'balance_amount' => (float) $order->balance_amount,
            'payment_status' => $order->payment_status->value,
            'processing_status_id' => $order->processing_status_id,
            'urgent' => $order->urgent,
            'created_at' => $order->created_at->format('H:i'),
        ];
    }

    /**
     * Cancel an order.
     *
     * DELETE /api/v1/pos/orders/{order}
     */
    public function destroy(Order $order): JsonResponse
    {
        abort_if(Gate::denies('order_delete'), Response::HTTP_FORBIDDEN);

        // Instead of deleting, we update the status to Cancelled (id: 1)
        $cancelledStatusId = 1;

        $this->posService->updateOrderStatus($order, $cancelledStatusId);

        return $this->success(null, 'Order cancelled successfully');
    }

    /**
     * Get completed orders history.
     *
     * GET /api/v1/pos/history
     */
    public function getHistory(Request $request): JsonResponse
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN);

        $search = trim($request->query('search', ''));

        $query = Order::with(['customer' => fn ($q) => $q->withTrashed(), 'orderItems', 'payments'])
            ->whereIn('processing_status_id', [1, 6]) // Cancelled (1) or Delivered (6)
            ->orderBy('updated_at', 'desc');

        // Apply search filter
        if ($search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($search, $searchLower) {
                // Search by Order Number
                $q->where('order_number', 'like', "%{$search}%")
                    // Or match Customer Name (case insensitive)
                    ->orWhereHas('customer', function ($cq) use ($searchLower) {
                        $cq->withTrashed()
                            ->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                    })
                    // Or match Customer Phone
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->withTrashed()
                            ->where('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Get total matching the query before limit
        $total = $query->clone()->count();

        $orders = $query->limit(50)->get();

        $formattedOrders = $orders->map(function ($order) {
            $lastPayment = $order->payments->last();
            $completedAt = $order->closed_at ?? $order->updated_at;

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->name ?? 'Unknown Customer',
                'customer_phone' => $order->customer?->phone ?? '-',
                'item_summary' => $this->getItemSummary($order),
                'service_name' => $order->orderItems->first()?->service_name ?? '',
                'total_amount' => (float) $order->total_amount,
                'payment_method' => $lastPayment?->payment_method?->value ?? 'cash',
                'payment_method_label' => $lastPayment?->payment_method?->getLabel() ?? 'Cash',
                'completed_at' => $completedAt?->format('Y-m-d H:i:s'),
                'processing_status' => $order->processing_status_id === 1 ? 'Cancelled' : 'Completed',
                'processing_status_id' => $order->processing_status_id,
            ];
        });

        // Calculate total revenue from displayed orders (only completed ones)
        $revenue = $orders->where('processing_status_id', 6)->sum('total_amount');

        return $this->success([
            'orders' => $formattedOrders,
            'total' => $total,
            'revenue' => (float) $revenue,
        ]);
    }

    /**
     * Get a summary of items for display.
     */
    protected function getItemSummary(Order $order): string
    {
        return $order->orderItems
            ->map(function ($item) {
                if ($item->pricing_type === 'weight' && $item->weight) {
                    return "{$item->item_name} {$item->weight}{$item->weight_unit}";
                }

                return "{$item->item_name} ×{$item->quantity}";
            })
            ->implode(', ');
    }
}
