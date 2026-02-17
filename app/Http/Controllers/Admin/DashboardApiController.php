<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function modules()
    {
        $modules = [
            [
                'id' => 'today_summary',
                'module' => 'today_summary',
                'component' => 'SummaryComponent',
                'columns' => 12,
                'title' => "Today's Summary",
                'isLoading' => false,
                'data' => $this->getTodaySummary(),
                'filters' => [],
                'query' => [],
            ],
            [
                'id' => 'order_stats',
                'module' => 'order_stats',
                'component' => 'SummaryComponent',
                'columns' => 6,
                'title' => 'Order Statistics',
                'isLoading' => false,
                'data' => $this->getOrderStats(),
                'filters' => [],
                'query' => [],
            ],
            [
                'id' => 'customer_stats',
                'module' => 'customer_stats',
                'component' => 'SummaryComponent',
                'columns' => 6,
                'title' => 'Customer Stats',
                'isLoading' => false,
                'data' => $this->getCustomerStats(),
                'filters' => [],
                'query' => [],
            ],
        ];

        return response()->json($modules);
    }

    public function fetchData(Request $request)
    {
        $module = $request->get('module');

        $data = match ($module) {
            'today_summary' => $this->getTodaySummary(),
            'order_stats' => $this->getOrderStats(),
            'customer_stats' => $this->getCustomerStats(),
            default => [],
        };

        return okResponse([
            'data' => $data,
        ]);
    }

    private function getTodaySummary(): array
    {
        $today = today();

        $todayOrders = Order::whereDate('order_date', $today)->count();
        $todayRevenue = (float) Payment::whereDate('payment_date', $today)->sum('amount');
        $pendingOrders = Order::whereIn('processing_status_id', [2, 3, 4])->count(); // Pending, Washing, Drying
        $readyForPickup = Order::where('processing_status_id', 5)->count(); // Ready

        return [
            ['id' => 'today_orders', 'label' => "Today's Orders", 'value' => (string) $todayOrders, 'variant' => 'primary'],
            ['id' => 'today_revenue', 'label' => "Today's Revenue", 'value' => number_format($todayRevenue, 2), 'variant' => 'success'],
            ['id' => 'pending', 'label' => 'In Progress', 'value' => (string) $pendingOrders, 'variant' => 'warning'],
            ['id' => 'ready', 'label' => 'Ready for Pickup', 'value' => (string) $readyForPickup, 'variant' => 'info'],
        ];
    }

    private function getOrderStats(): array
    {
        $thisMonth = now()->startOfMonth();
        $monthlyOrders = Order::where('order_date', '>=', $thisMonth)->count();
        $monthlyRevenue = (float) Order::where('order_date', '>=', $thisMonth)->sum('total_amount');
        $urgentOrders = Order::where('urgent', true)->whereIn('processing_status_id', [2, 3, 4, 5])->count();
        $overdueOrders = Order::where('promised_date', '<', today())
            ->whereNotIn('processing_status_id', [1, 6]) // Not cancelled or delivered
            ->count();

        return [
            ['id' => 'monthly_orders', 'label' => 'This Month Orders', 'value' => (string) $monthlyOrders, 'variant' => 'primary'],
            ['id' => 'monthly_revenue', 'label' => 'Monthly Revenue', 'value' => number_format($monthlyRevenue, 2), 'variant' => 'success'],
            ['id' => 'urgent', 'label' => 'Urgent Orders', 'value' => (string) $urgentOrders, 'variant' => 'danger'],
            ['id' => 'overdue', 'label' => 'Overdue', 'value' => (string) $overdueOrders, 'variant' => 'danger'],
        ];
    }

    private function getCustomerStats(): array
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $newThisMonth = Customer::where('created_at', '>=', now()->startOfMonth())->count();
        $outstandingBalance = (float) Order::whereNotIn('processing_status_id', [1])
            ->where('balance_amount', '>', 0)
            ->sum('balance_amount');

        return [
            ['id' => 'total_customers', 'label' => 'Total Customers', 'value' => (string) $totalCustomers, 'variant' => 'primary'],
            ['id' => 'active_customers', 'label' => 'Active Customers', 'value' => (string) $activeCustomers, 'variant' => 'success'],
            ['id' => 'new_customers', 'label' => 'New This Month', 'value' => (string) $newThisMonth, 'variant' => 'info'],
            ['id' => 'outstanding', 'label' => 'Outstanding Balance', 'value' => number_format($outstandingBalance, 2), 'variant' => 'warning'],
        ];
    }
}
