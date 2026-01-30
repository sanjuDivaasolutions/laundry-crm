<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderStatusTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private int $tenantId = 1;

    private int $paymentNumber = 1000;

    public function run(): void
    {
        // Get customers
        $customers = Customer::where('tenant_id', $this->tenantId)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');

            return;
        }

        // Get items and services
        $items = Item::where('tenant_id', $this->tenantId)->get();
        $services = Service::where('tenant_id', $this->tenantId)->get();

        if ($items->isEmpty() || $services->isEmpty()) {
            $this->command->warn('No items or services found. Please run ItemSeeder and ServiceSeeder first.');

            return;
        }

        // Get all statuses
        $statuses = $this->getAllStatuses();

        $orderNumber = 1000;

        // Create orders representing all possible states
        $this->command->info('Creating orders in various states...');

        // 1. PENDING ORDERS (New orders, unpaid)
        $this->createPendingOrders($customers, $items, $services, $statuses, $orderNumber);
        $orderNumber += 3;

        // 2. WASHING ORDERS (In progress, partial payment)
        $this->createWashingOrders($customers, $items, $services, $statuses, $orderNumber);
        $orderNumber += 3;

        // 3. DRYING ORDERS (In progress, various payment states)
        $this->createDryingOrders($customers, $items, $services, $statuses, $orderNumber);
        $orderNumber += 2;

        // 4. READY ORDERS (Ready for pickup, fully paid and unpaid)
        $this->createReadyOrders($customers, $items, $services, $statuses, $orderNumber);
        $orderNumber += 4;

        // 5. DELIVERED ORDERS (Completed, closed)
        $this->createDeliveredOrders($customers, $items, $services, $statuses, $orderNumber);
        $orderNumber += 5;

        // 6. URGENT ORDERS (Various states, marked urgent)
        $this->createUrgentOrders($customers, $items, $services, $statuses, $orderNumber);
        $orderNumber += 3;

        $this->command->info('Created '.($orderNumber - 1000).' orders with complete history.');
    }

    private function getAllStatuses(): array
    {
        return [
            'processing' => [
                'pending' => ProcessingStatus::where('status_name', ProcessingStatusEnum::Pending->value)->first(),
                'washing' => ProcessingStatus::where('status_name', ProcessingStatusEnum::Washing->value)->first(),
                'drying' => ProcessingStatus::where('status_name', ProcessingStatusEnum::Drying->value)->first(),
                'ready' => ProcessingStatus::where('status_name', ProcessingStatusEnum::Ready->value)->first(),
                'delivered' => ProcessingStatus::where('status_name', ProcessingStatusEnum::Delivered->value)->first(),
            ],
            'order' => [
                'open' => OrderStatus::where('status_name', OrderStatusEnum::Open->value)->first(),
                'closed' => OrderStatus::where('status_name', OrderStatusEnum::Closed->value)->first(),
            ],
        ];
    }

    private function createPendingOrders($customers, $items, $services, $statuses, &$orderNumber): void
    {
        $this->command->info('  → Creating PENDING orders (new, unpaid)...');

        for ($i = 0; $i < 3; $i++) {
            $order = $this->createBaseOrder(
                $customers->random(),
                $orderNumber++,
                $statuses['processing']['pending'],
                $statuses['order']['open'],
                PaymentStatusEnum::Unpaid,
                false,
                now()->subHours(rand(1, 12))
            );

            $this->addItemsToOrder($order, $items, $services, rand(2, 4));

            // Record initial status
            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Processing,
                null,
                $statuses['processing']['pending']->id,
                'Order received and logged',
                $order->order_date
            );

            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Order,
                null,
                $statuses['order']['open']->id,
                'Order opened',
                $order->order_date
            );
        }
    }

    private function createWashingOrders($customers, $items, $services, $statuses, &$orderNumber): void
    {
        $this->command->info('  → Creating WASHING orders (in progress, partial payment)...');

        for ($i = 0; $i < 3; $i++) {
            $order = $this->createBaseOrder(
                $customers->random(),
                $orderNumber++,
                $statuses['processing']['washing'],
                $statuses['order']['open'],
                PaymentStatusEnum::Partial,
                $i === 0, // First one is urgent
                now()->subDays(rand(1, 2))
            );

            $this->addItemsToOrder($order, $items, $services, rand(3, 5));

            // Status history: Pending → Washing
            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Processing,
                null,
                $statuses['processing']['pending']->id,
                'Order received',
                $order->order_date
            );

            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Processing,
                $statuses['processing']['pending']->id,
                $statuses['processing']['washing']->id,
                'Items sorted and washing started',
                $order->order_date->addHours(2)
            );

            // Partial payment
            $this->addPayment(
                $order,
                $order->total_amount * 0.5,
                PaymentMethodEnum::Cash,
                'Advance payment',
                $order->order_date->addMinutes(30)
            );
        }
    }

    private function createDryingOrders($customers, $items, $services, $statuses, &$orderNumber): void
    {
        $this->command->info('  → Creating DRYING orders (in progress, various payments)...');

        for ($i = 0; $i < 2; $i++) {
            $paymentStatus = $i === 0 ? PaymentStatusEnum::Paid : PaymentStatusEnum::Partial;

            $order = $this->createBaseOrder(
                $customers->random(),
                $orderNumber++,
                $statuses['processing']['drying'],
                $statuses['order']['open'],
                $paymentStatus,
                false,
                now()->subDays(rand(1, 3))
            );

            $this->addItemsToOrder($order, $items, $services, rand(2, 4));

            // Status history: Pending → Washing → Drying
            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Processing,
                null,
                $statuses['processing']['pending']->id,
                'Order received',
                $order->order_date
            );

            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Processing,
                $statuses['processing']['pending']->id,
                $statuses['processing']['washing']->id,
                'Washing in progress',
                $order->order_date->addHours(3)
            );

            $this->recordStatusChange(
                $order,
                OrderStatusTypeEnum::Processing,
                $statuses['processing']['washing']->id,
                $statuses['processing']['drying']->id,
                'Moved to drying',
                $order->order_date->addHours(5)
            );

            // Add payments based on status
            if ($paymentStatus === PaymentStatusEnum::Paid) {
                $this->addPayment($order, $order->total_amount, PaymentMethodEnum::Card, 'Full payment', $order->order_date->addMinutes(15));
            } else {
                $this->addPayment($order, $order->total_amount * 0.3, PaymentMethodEnum::Upi, 'Partial payment', $order->order_date->addMinutes(20));
            }
        }
    }

    private function createReadyOrders($customers, $items, $services, $statuses, &$orderNumber): void
    {
        $this->command->info('  → Creating READY orders (ready for pickup)...');

        for ($i = 0; $i < 4; $i++) {
            $paymentStatus = match ($i) {
                0, 1 => PaymentStatusEnum::Paid,
                2 => PaymentStatusEnum::Partial,
                default => PaymentStatusEnum::Unpaid,
            };

            $order = $this->createBaseOrder(
                $customers->random(),
                $orderNumber++,
                $statuses['processing']['ready'],
                $statuses['order']['open'],
                $paymentStatus,
                $i === 3, // Last one is urgent
                now()->subDays(rand(2, 4))
            );

            $this->addItemsToOrder($order, $items, $services, rand(3, 6));

            // Complete status history: Pending → Washing → Drying → Ready
            $baseTime = $order->order_date;
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, null, $statuses['processing']['pending']->id, 'Order received', $baseTime);
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['pending']->id, $statuses['processing']['washing']->id, 'Washing started', $baseTime->copy()->addHours(1));
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['washing']->id, $statuses['processing']['drying']->id, 'Drying started', $baseTime->copy()->addHours(4));
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['drying']->id, $statuses['processing']['ready']->id, 'Items ready for pickup', $baseTime->copy()->addHours(7));

            // Add payments
            if ($paymentStatus === PaymentStatusEnum::Paid) {
                if ($i === 0) {
                    // Full payment upfront
                    $this->addPayment($order, $order->total_amount, PaymentMethodEnum::Card, 'Full payment at order', $baseTime->addMinutes(10));
                } else {
                    // Multiple payments
                    $this->addPayment($order, $order->total_amount * 0.6, PaymentMethodEnum::Cash, 'Advance payment', $baseTime->addMinutes(15));
                    $this->addPayment($order, $order->total_amount * 0.4, PaymentMethodEnum::Upi, 'Balance payment', $baseTime->addHours(6));
                }
            } elseif ($paymentStatus === PaymentStatusEnum::Partial) {
                $this->addPayment($order, $order->total_amount * 0.7, PaymentMethodEnum::Cash, 'Partial payment', $baseTime->addHours(2));
            }
        }
    }

    private function createDeliveredOrders($customers, $items, $services, $statuses, &$orderNumber): void
    {
        $this->command->info('  → Creating DELIVERED orders (completed, closed)...');

        for ($i = 0; $i < 5; $i++) {
            $order = $this->createBaseOrder(
                $customers->random(),
                $orderNumber++,
                $statuses['processing']['delivered'],
                $statuses['order']['closed'],
                PaymentStatusEnum::Paid,
                false,
                now()->subDays(rand(5, 15))
            );

            $order->update([
                'picked_up_at' => now()->subDays(rand(1, 4)),
                'closed_at' => now()->subDays(rand(1, 4)),
            ]);

            $this->addItemsToOrder($order, $items, $services, rand(2, 5));

            // Complete lifecycle: Pending → Washing → Drying → Ready → Delivered
            $baseTime = $order->order_date;
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, null, $statuses['processing']['pending']->id, 'Order received', $baseTime);
            $this->recordStatusChange($order, OrderStatusTypeEnum::Order, null, $statuses['order']['open']->id, 'Order opened', $baseTime);
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['pending']->id, $statuses['processing']['washing']->id, 'Washing started', $baseTime->copy()->addHours(2));
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['washing']->id, $statuses['processing']['drying']->id, 'Drying started', $baseTime->copy()->addHours(5));
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['drying']->id, $statuses['processing']['ready']->id, 'Ready for pickup', $baseTime->copy()->addHours(8));
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['ready']->id, $statuses['processing']['delivered']->id, 'Delivered to customer', $order->picked_up_at);
            $this->recordStatusChange($order, OrderStatusTypeEnum::Order, $statuses['order']['open']->id, $statuses['order']['closed']->id, 'Order completed and closed', $order->closed_at);

            // Payment history - various scenarios
            $paymentMethods = [PaymentMethodEnum::Cash, PaymentMethodEnum::Card, PaymentMethodEnum::Upi];
            if ($i < 2) {
                // Full payment at delivery
                $this->addPayment($order, $order->total_amount, $paymentMethods[array_rand($paymentMethods)], 'Payment at delivery', $order->picked_up_at);
            } else {
                // Split payments
                $this->addPayment($order, $order->total_amount * 0.5, PaymentMethodEnum::Cash, 'Advance payment', $baseTime->addMinutes(20));
                $this->addPayment($order, $order->total_amount * 0.5, PaymentMethodEnum::Card, 'Balance at pickup', $order->picked_up_at);
            }
        }
    }

    private function createUrgentOrders($customers, $items, $services, $statuses, &$orderNumber): void
    {
        $this->command->info('  → Creating URGENT orders (high priority)...');

        $urgentStatuses = [
            ['processing' => $statuses['processing']['washing'], 'order' => $statuses['order']['open'], 'payment' => PaymentStatusEnum::Paid],
            ['processing' => $statuses['processing']['drying'], 'order' => $statuses['order']['open'], 'payment' => PaymentStatusEnum::Partial],
            ['processing' => $statuses['processing']['ready'], 'order' => $statuses['order']['open'], 'payment' => PaymentStatusEnum::Paid],
        ];

        foreach ($urgentStatuses as $statusSet) {
            $order = $this->createBaseOrder(
                $customers->random(),
                $orderNumber++,
                $statusSet['processing'],
                $statusSet['order'],
                $statusSet['payment'],
                true, // URGENT
                now()->subHours(rand(6, 24))
            );

            $this->addItemsToOrder($order, $items, $services, rand(1, 3));

            // Status history with urgent remarks
            $baseTime = $order->order_date;
            $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, null, $statuses['processing']['pending']->id, 'URGENT: Express service requested', $baseTime);
            $this->recordStatusChange($order, OrderStatusTypeEnum::Order, null, $statuses['order']['open']->id, 'URGENT order opened', $baseTime);

            if ($statusSet['processing']->id >= $statuses['processing']['washing']->id) {
                $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['pending']->id, $statuses['processing']['washing']->id, 'URGENT: Priority washing', $baseTime->copy()->addMinutes(30));
            }

            if ($statusSet['processing']->id >= $statuses['processing']['drying']->id) {
                $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['washing']->id, $statuses['processing']['drying']->id, 'URGENT: Fast drying', $baseTime->copy()->addHours(2));
            }

            if ($statusSet['processing']->id >= $statuses['processing']['ready']->id) {
                $this->recordStatusChange($order, OrderStatusTypeEnum::Processing, $statuses['processing']['drying']->id, $statuses['processing']['ready']->id, 'URGENT: Ready ahead of schedule', $baseTime->copy()->addHours(4));
            }

            // Payments for urgent orders
            if ($statusSet['payment'] === PaymentStatusEnum::Paid) {
                $this->addPayment($order, $order->total_amount, PaymentMethodEnum::Card, 'Express service - full payment', $baseTime->addMinutes(5));
            } else {
                $this->addPayment($order, $order->total_amount * 0.8, PaymentMethodEnum::Upi, 'Urgent order advance', $baseTime->addMinutes(10));
            }
        }
    }

    private function createBaseOrder($customer, $orderNumber, $processingStatus, $orderStatus, $paymentStatus, $urgent, $orderDate): Order
    {
        return Order::create([
            'tenant_id' => $this->tenantId,
            'order_number' => 'ORD-'.$orderNumber,
            'customer_id' => $customer->id,
            'order_date' => $orderDate,
            'promised_date' => $urgent ? $orderDate->copy()->addHours(24) : $orderDate->copy()->addDays(rand(2, 5)),
            'total_items' => 0,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
            'payment_status' => $paymentStatus,
            'processing_status_id' => $processingStatus->id,
            'order_status_id' => $orderStatus->id,
            'created_by_employee_id' => 1,
            'urgent' => $urgent,
            'tax_rate' => 10.00,
            'tax_amount' => 0,
            'discount_type' => 'fixed',
        ]);
    }

    private function addItemsToOrder($order, $items, $services, $numItems): void
    {
        $orderSubtotal = 0;

        for ($j = 0; $j < $numItems; $j++) {
            $item = $items->random();
            $service = $services->random();

            $servicePrice = ServicePrice::where('tenant_id', $this->tenantId)
                ->where('item_id', $item->id)
                ->where('service_id', $service->id)
                ->first();

            $unitPrice = $servicePrice?->price ?? $item->price;
            $quantity = rand(1, 3);
            $totalPrice = $unitPrice * $quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'category_id' => $item->category_id,
                'item_id' => $item->id,
                'service_id' => $service->id,
                'item_name' => $item->name,
                'service_name' => $service->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'barcode' => 'BC-'.str_pad(explode('-', $order->order_number)[1], 6, '0', STR_PAD_LEFT).'-'.str_pad($j + 1, 3, '0', STR_PAD_LEFT),
                'color' => ['White', 'Blue', 'Black', 'Red', 'Green', 'Gray'][array_rand(['White', 'Blue', 'Black', 'Red', 'Green', 'Gray'])],
                'brand' => ['Nike', 'Adidas', 'Zara', 'H&M', 'Uniqlo', 'Gap', null][array_rand(['Nike', 'Adidas', 'Zara', 'H&M', 'Uniqlo', 'Gap', null])],
                'defect_notes' => rand(0, 10) > 7 ? 'Minor stain detected' : null,
                'notes' => $order->urgent ? 'URGENT - Handle with priority' : null,
            ]);

            $orderSubtotal += $totalPrice;
        }

        // Calculate order totals
        $taxAmount = round($orderSubtotal * ($order->tax_rate / 100), 2);
        $discountAmount = $order->urgent ? 0 : (rand(0, 10) > 7 ? rand(5, 20) : 0);
        $totalAmount = $orderSubtotal + $taxAmount - $discountAmount;

        $order->update([
            'total_items' => $numItems,
            'subtotal' => $orderSubtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'balance_amount' => $totalAmount, // Will be updated after payments
        ]);
    }

    private function addPayment($order, $amount, $paymentMethod, $notes, $paymentDate): void
    {
        $this->paymentNumber++;

        Payment::create([
            'tenant_id' => $this->tenantId,
            'payment_number' => 'PAY-'.$this->paymentNumber,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'payment_date' => $paymentDate,
            'amount' => round($amount, 2),
            'payment_method' => $paymentMethod,
            'transaction_reference' => $paymentMethod === PaymentMethodEnum::Card ? 'TXN-'.strtoupper(substr(md5($this->paymentNumber), 0, 12)) : null,
            'notes' => $notes,
            'received_by_employee_id' => 1,
        ]);

        // Update order paid amount and balance
        $totalPaid = Payment::where('order_id', $order->id)->sum('amount');
        $balance = $order->total_amount - $totalPaid;

        $paymentStatus = match (true) {
            $balance <= 0 => PaymentStatusEnum::Paid,
            $totalPaid > 0 => PaymentStatusEnum::Partial,
            default => PaymentStatusEnum::Unpaid,
        };

        $order->update([
            'paid_amount' => $totalPaid,
            'balance_amount' => max(0, $balance),
            'payment_status' => $paymentStatus,
        ]);

        // Record payment status change
        $this->recordStatusChange(
            $order,
            OrderStatusTypeEnum::Payment,
            null,
            null,
            'Payment received: '.$paymentMethod->getLabel().' - '.number_format($amount, 2),
            $paymentDate
        );
    }

    private function recordStatusChange($order, $statusType, $oldStatusId, $newStatusId, $remarks, $changedAt): void
    {
        OrderStatusHistory::create([
            'tenant_id' => $this->tenantId,
            'order_id' => $order->id,
            'status_type' => $statusType,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'changed_by_employee_id' => 1,
            'remarks' => $remarks,
            'changed_at' => $changedAt,
        ]);
    }
}
