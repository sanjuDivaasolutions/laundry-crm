<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;

class ReportService
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Get daily summary for a given date.
     */
    public function getDailySummary(?string $date = null): array
    {
        $date = $date ? Carbon::parse($date) : today();

        $orders = Order::whereDate('order_date', $date);
        $payments = Payment::whereDate('payment_date', $date);

        return [
            'date' => $date->format('Y-m-d'),
            'total_orders' => $orders->count(),
            'total_items' => (int) $orders->sum('total_items'),
            'total_revenue' => (float) $orders->sum('total_amount'),
            'total_collected' => (float) $payments->sum('amount'),
            'pending_amount' => (float) $orders->sum('balance_amount'),
            'urgent_orders' => $orders->clone()->where('urgent', true)->count(),
            'new_customers' => Customer::whereDate('created_at', $date)->count(),
        ];
    }

    /**
     * Get weekly summary.
     */
    public function getWeeklySummary(?string $startDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfWeek() : now()->startOfWeek();
        $end = $start->copy()->endOfWeek();

        return $this->getRangeSummary($start, $end, 'week');
    }

    /**
     * Get monthly summary.
     */
    public function getMonthlySummary(?string $month = null): array
    {
        $date = $month ? Carbon::parse($month) : now();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return $this->getRangeSummary($start, $end, 'month');
    }

    /**
     * Get revenue trend (daily breakdown for a date range).
     */
    public function getRevenueTrend(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $dailyRevenue = Order::whereBetween('order_date', [$start, $end])
            ->selectRaw('DATE(order_date) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupByRaw('DATE(order_date)')
            ->orderByRaw('DATE(order_date)')
            ->get();

        $dailyPayments = Payment::whereBetween('payment_date', [$start, $end])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as collected')
            ->groupByRaw('DATE(payment_date)')
            ->pluck('collected', 'date');

        return $dailyRevenue->map(fn ($day) => [
            'date' => $day->date,
            'orders' => (int) $day->orders,
            'revenue' => (float) $day->revenue,
            'collected' => (float) ($dailyPayments[$day->date] ?? 0),
        ])->values()->all();
    }

    /**
     * Get top services by revenue.
     */
    public function getTopServices(string $startDate, string $endDate, int $limit = 10): array
    {
        return OrderItem::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('service_name, COUNT(*) as order_count, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
            ->groupBy('service_name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'service_name' => $item->service_name,
                'order_count' => (int) $item->order_count,
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ])
            ->all();
    }

    /**
     * Get top customers by revenue.
     */
    public function getTopCustomers(string $startDate, string $endDate, int $limit = 10): array
    {
        return Order::whereBetween('order_date', [$startDate, $endDate])
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->selectRaw('customers.id, customers.name, customers.phone, COUNT(orders.id) as order_count, SUM(orders.total_amount) as total_spent')
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'order_count' => (int) $c->order_count,
                'total_spent' => (float) $c->total_spent,
            ])
            ->all();
    }

    /**
     * Get payment method breakdown.
     */
    public function getPaymentMethodBreakdown(string $startDate, string $endDate): array
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($p) => [
                'method' => $p->payment_method,
                'count' => (int) $p->count,
                'total' => (float) $p->total,
            ])
            ->all();
    }

    /**
     * Get order status distribution.
     */
    public function getStatusDistribution(string $startDate, string $endDate): array
    {
        return Order::whereBetween('order_date', [$startDate, $endDate])
            ->join('processing_status', 'orders.processing_status_id', '=', 'processing_status.id')
            ->selectRaw('processing_status.status_name, COUNT(*) as count')
            ->groupBy('processing_status.status_name')
            ->get()
            ->map(fn ($s) => [
                'status' => $s->status_name,
                'count' => (int) $s->count,
            ])
            ->all();
    }

    /**
     * Get range summary for weekly/monthly reports.
     */
    protected function getRangeSummary(Carbon $start, Carbon $end, string $period): array
    {
        $orders = Order::whereBetween('order_date', [$start, $end]);
        $payments = Payment::whereBetween('payment_date', [$start, $end]);

        $totalOrders = $orders->count();
        $totalRevenue = (float) $orders->sum('total_amount');
        $totalCollected = (float) $payments->sum('amount');

        return [
            'period' => $period,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_orders' => $totalOrders,
            'total_items' => (int) $orders->sum('total_items'),
            'total_revenue' => $totalRevenue,
            'total_collected' => $totalCollected,
            'outstanding_amount' => (float) $orders->sum('balance_amount'),
            'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            'urgent_orders' => $orders->clone()->where('urgent', true)->count(),
            'new_customers' => Customer::whereBetween('created_at', [$start, $end])->count(),
            'top_services' => $this->getTopServices($start->toDateString(), $end->toDateString(), 5),
            'top_customers' => $this->getTopCustomers($start->toDateString(), $end->toDateString(), 5),
            'payment_methods' => $this->getPaymentMethodBreakdown($start->toDateString(), $end->toDateString()),
            'status_distribution' => $this->getStatusDistribution($start->toDateString(), $end->toDateString()),
        ];
    }
}
