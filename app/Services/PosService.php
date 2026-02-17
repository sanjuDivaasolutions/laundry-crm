<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatusTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PosService
 *
 * Business logic for the POS Kanban board operations.
 */
class PosService
{
    public function __construct(
        protected TenantService $tenantService,
        protected LoyaltyService $loyaltyService
    ) {}

    /**
     * Get all data needed for the Kanban board.
     */
    public function getKanbanData(): array
    {
        $tenant = $this->tenantService->getTenant();

        // Get today's orders grouped by processing status
        $orders = Order::with(['customer', 'orderItems', 'processingStatus'])
            ->whereDate('order_date', today())
            ->orderBy('created_at', 'desc')
            ->get();

        // Get processing statuses for columns (exclude Delivered and Cancelled for main board)
        $statuses = ProcessingStatus::active()
            ->ordered()
            ->whereNotIn('id', [1, 6]) // Exclude Cancelled (1) and Delivered (6)
            ->get();

        // Group orders by status
        $ordersByStatus = [];
        foreach ($statuses as $status) {
            $ordersByStatus[$status->id] = $orders->where('processing_status_id', $status->id)->values();
        }

        return [
            'statuses' => $statuses,
            'orders_by_status' => $ordersByStatus,
            'statistics' => $this->getStatistics(),
            'items' => $this->getItemsWithPrices(),
            'services' => Service::where('is_active', true)->get(['id', 'name']),
        ];
    }

    /**
     * Get statistics for the status bar.
     */
    public function getStatistics(): array
    {
        $today = today();

        $counts = Order::whereDate('order_date', $today)
            ->selectRaw('processing_status_id, COUNT(*) as count')
            ->groupBy('processing_status_id')
            ->pluck('count', 'processing_status_id')
            ->toArray();

        // Today's revenue (completed orders)
        $todayRevenue = Payment::whereDate('payment_date', $today)
            ->sum('amount');

        // ProcessingStatus IDs: 2=Pending, 3=Washing, 4=Drying, 5=Ready, 6=Delivered
        return [
            'pending' => $counts[2] ?? 0,
            'washing' => $counts[3] ?? 0,
            'drying' => $counts[4] ?? 0,
            'ready' => $counts[5] ?? 0,
            'completed' => $counts[6] ?? 0,
            'today_revenue' => (float) $todayRevenue,
        ];
    }

    /**
     * Get items with their service prices.
     */
    public function getItemsWithPrices(): Collection
    {
        return Item::with(['servicePrices.service'])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'price' => $item->price,
                    'service_prices' => $item->servicePrices->map(fn ($sp) => [
                        'service_id' => $sp->service_id,
                        'service_name' => $sp->service?->name,
                        'price' => $sp->price,
                    ]),
                ];
            });
    }

    /**
     * Create a quick order from POS.
     */
    public function createQuickOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $tenant = $this->tenantService->getTenant();

            // Find or create customer
            $customer = $this->findOrCreateCustomer(
                $data['customer_name'],
                $data['customer_phone']
            );

            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Calculate totals
            $totals = $this->calculateOrderTotals($data['items'], $data['service_id']);

            // Create order
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'order_date' => now(),
                'promised_date' => $data['promised_date'] ?? now()->addDay(),
                'total_items' => $totals['total_items'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => 0,
                'total_amount' => $totals['total_amount'],
                'paid_amount' => 0,
                'balance_amount' => $totals['total_amount'],
                'payment_status' => PaymentStatusEnum::Unpaid,
                'processing_status_id' => 2, // Pending
                'order_status_id' => 1, // Open
                'urgent' => $data['urgent'] ?? false,
                'notes' => $data['notes'] ?? null,
                'created_by_employee_id' => auth()->id(),
            ]);

            // Create order items
            $service = Service::find($data['service_id']);

            foreach ($data['items'] as $itemData) {
                $item = Item::find($itemData['item_id']);

                // Get price from service_prices or default item price
                $servicePrice = ServicePrice::where('item_id', $item->id)
                    ->where('service_id', $data['service_id'])
                    ->first();

                $unitPrice = $servicePrice?->price ?? $item->price;
                $quantity = $itemData['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'service_id' => $data['service_id'],
                    'item_name' => $item->name,
                    'service_name' => $service->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Log initial status
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_type' => OrderStatusTypeEnum::Processing,
                'old_status_id' => null,
                'new_status_id' => 2, // Pending
                'changed_by_employee_id' => auth()->id(),
                'remarks' => 'Order created',
                'changed_at' => now(),
            ]);

            return $order->load(['customer', 'orderItems', 'processingStatus']);
        });
    }

    /**
     * Update order processing status.
     */
    public function updateOrderStatus(Order $order, int $newStatusId): Order
    {
        $oldStatusId = $order->processing_status_id;

        $order->update([
            'processing_status_id' => $newStatusId,
        ]);

        // If moving to Ready (ID 5), set actual_ready_date
        if ($newStatusId === 5) {
            $order->update(['actual_ready_date' => now()]);
        }

        // Log status change
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status_type' => OrderStatusTypeEnum::Processing,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'changed_by_employee_id' => auth()->id(),
            'remarks' => 'Status updated via POS',
            'changed_at' => now(),
        ]);

        $freshOrder = $order->fresh(['customer', 'orderItems', 'processingStatus']);

        // Send notification to order creator
        $this->dispatchStatusNotification($freshOrder, $newStatusId);

        return $freshOrder;
    }

    /**
     * Process payment and complete order.
     */
    public function processPayment(Order $order, array $paymentData): array
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $amount = (float) $paymentData['amount'];

            // Create payment record
            $payment = Payment::create([
                'tenant_id' => $order->tenant_id,
                'payment_number' => $this->generatePaymentNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'payment_date' => now(),
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'],
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'received_by_employee_id' => auth()->id(),
            ]);

            // Update order payment
            $newPaidAmount = $order->paid_amount + $amount;
            $newBalanceAmount = $order->total_amount - $newPaidAmount;

            $paymentStatus = PaymentStatusEnum::Unpaid;
            if ($newBalanceAmount <= 0) {
                $paymentStatus = PaymentStatusEnum::Paid;
                $newBalanceAmount = 0;
            } elseif ($newPaidAmount > 0) {
                $paymentStatus = PaymentStatusEnum::Partial;
            }

            $order->update([
                'paid_amount' => $newPaidAmount,
                'balance_amount' => $newBalanceAmount,
                'payment_status' => $paymentStatus,
            ]);

            // If fully paid, mark as delivered and award loyalty points
            if ($paymentStatus === PaymentStatusEnum::Paid) {
                $this->updateOrderStatus($order, 6); // Delivered
                $order->update([
                    'picked_up_at' => now(),
                    'closed_at' => now(),
                    'order_status_id' => 2, // Closed
                ]);

                $this->loyaltyService->awardPointsForOrder($order);
            }

            return [
                'payment' => $payment,
                'order' => $order->fresh(['customer', 'orderItems', 'processingStatus']),
            ];
        });
    }

    /**
     * Find existing customer or create new one.
     */
    protected function findOrCreateCustomer(string $name, string $phone): Customer
    {
        $tenant = $this->tenantService->getTenant();

        $customer = Customer::where('phone', $phone)->first();

        if (! $customer) {
            $customer = Customer::create([
                'tenant_id' => $tenant->id,
                'customer_code' => $this->generateCustomerCode(),
                'name' => $name,
                'phone' => $phone,
                'is_active' => true,
            ]);
        } else {
            // Update name if different
            if ($customer->name !== $name) {
                $customer->update(['name' => $name]);
            }
        }

        return $customer;
    }

    /**
     * Generate unique customer code.
     */
    protected function generateCustomerCode(): string
    {
        $prefix = 'CUST';
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        $sequence = $lastCustomer ? $lastCustomer->id + 1 : 1;

        return sprintf('%s%06d', $prefix, $sequence);
    }

    /**
     * Calculate order totals from items.
     */
    protected function calculateOrderTotals(array $items, int $serviceId): array
    {
        $subtotal = 0;
        $totalItems = 0;

        foreach ($items as $itemData) {
            $item = Item::find($itemData['item_id']);
            $quantity = $itemData['quantity'];

            // Get price from service_prices or default item price
            $servicePrice = ServicePrice::where('item_id', $item->id)
                ->where('service_id', $serviceId)
                ->first();

            $unitPrice = $servicePrice?->price ?? $item->price;
            $subtotal += $unitPrice * $quantity;
            $totalItems += $quantity;
        }

        return [
            'subtotal' => $subtotal,
            'total_amount' => $subtotal, // Can add tax calculation here
            'total_items' => $totalItems,
        ];
    }

    /**
     * Generate unique order number.
     */
    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('ymd');
        $lastOrder = Order::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    /**
     * Generate unique payment number.
     */
    protected function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('ymd');

        // Payment model has no timestamps, use payment_date instead
        $lastPayment = Payment::whereDate('payment_date', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPayment ? ((int) substr($lastPayment->payment_number, -4)) + 1 : 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    /**
     * Dispatch notification when order status changes.
     */
    protected function dispatchStatusNotification(Order $order, int $newStatusId): void
    {
        $newStatus = ProcessingStatus::find($newStatusId);
        $statusName = $newStatus?->status_name ?? 'Unknown';

        $statusType = match ($statusName) {
            ProcessingStatusEnum::Ready->value => 'ready',
            ProcessingStatusEnum::Delivered->value => 'completed',
            default => 'processing',
        };

        // Notify the order creator (staff member)
        $creator = User::find($order->created_by_employee_id);
        if ($creator) {
            $creator->notify(new OrderStatusNotification($order, $statusType, $statusName));
        }
    }
}
