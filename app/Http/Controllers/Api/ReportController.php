<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\GeneralResource;
use App\Http\Resources\Admin\Reports\SalesByMonthResourceCollection;
use App\Http\Resources\Admin\Reports\SalesByProductResource;
use App\Http\Resources\Admin\Reports\InwardsByProductResource;
use App\Http\Resources\Admin\Reports\SalesCommissionResource;
use App\Services\ReportService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportController extends Controller
{
    protected ReportService $reportService;
    protected OrderService $orderService;

    public function __construct(ReportService $reportService, OrderService $orderService)
    {
        $this->reportService = $reportService;
        $this->orderService = $orderService;
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $report = ReportService::getProfitLoss();

        return response()->json($report);
    }

    public function stockSummary(Request $request): ResourceCollection
    {
        return ReportService::getSummaryStock();
    }

    public function salesByProduct(Request $request): ResourceCollection
    {
        return ReportService::getSalesByProduct();
    }

    public function salesByProductDetails($productId, Request $request): JsonResponse
    {
        $requestFilters = $request->only(['f_date_range', 'limit']);
        $details = ReportService::getProductSaleDetails($productId, $requestFilters);

        return response()->json($details);
    }

    public function inwardsByProduct(Request $request): ResourceCollection
    {
        return ReportService::getInwardsByProduct();
    }

    public function inwardsByProductDetails($productId, Request $request): JsonResponse
    {
        $requestFilters = $request->only(['f_date_range', 'limit']);
        $details = ReportService::getProductInwardDetails($productId, $requestFilters);

        return response()->json($details);
    }

    public function salesCommission(Request $request): ResourceCollection
    {
        return ReportService::getSalesCommission();
    }

    public function agentCommissionDetails($agentId, Request $request): JsonResponse
    {
        $requestFilters = $request->only(['f_date_range', 'limit']);
        $details = ReportService::getAgentCommissionDetails($agentId, $requestFilters);

        return response()->json($details);
    }

    public function salesByMonth(Request $request): SalesByMonthResourceCollection
    {
        return ReportService::getSalesByMonth();
    }

    public function commissionSummary(Request $request): JsonResponse
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
            $summary = $this->orderService->getCommissionSummary($company, $startDate, $endDate);
        } else {
            // Get summary for all companies
            $summary = [
                'total_commissions' => \App\Models\AgentCommission::count(),
                'total_commission_amount' => \App\Models\AgentCommission::sum('commission_amount'),
                'pending_commissions' => \App\Models\AgentCommission::where('status', 'pending')->count(),
                'pending_amount' => \App\Models\AgentCommission::where('status', 'pending')->sum('commission_amount'),
                'approved_commissions' => \App\Models\AgentCommission::where('status', 'approved')->count(),
                'approved_amount' => \App\Models\AgentCommission::where('status', 'approved')->sum('commission_amount'),
                'paid_commissions' => \App\Models\AgentCommission::where('status', 'paid')->count(),
                'paid_amount' => \App\Models\AgentCommission::where('status', 'paid')->sum('commission_amount'),
            ];
        }

        return response()->json($summary);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $companyId = $request->get('company_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $data = [];

        // Sales Overview
        $salesQuery = \App\Models\SalesInvoice::query();
        if ($companyId) {
            $salesQuery->where('company_id', $companyId);
        }
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('date', [$startDate, $endDate]);
        }

        $data['sales_overview'] = [
            'total_sales' => $salesQuery->count(),
            'total_revenue' => $salesQuery->sum('grand_total'),
            'average_sale_value' => $salesQuery->avg('grand_total') ?? 0,
        ];

        // Order Statistics
        if ($companyId) {
            $company = \App\Models\Company::findOrFail($companyId);
            $data['order_statistics'] = $this->orderService->getOrderStatistics($company, $startDate, $endDate);
            $data['commission_summary'] = $this->orderService->getCommissionSummary($company, $startDate, $endDate);
        } else {
            $orderQuery = \App\Models\SalesOrder::query();
            if ($startDate && $endDate) {
                $orderQuery->whereBetween('date', [$startDate, $endDate]);
            }

            $data['order_statistics'] = [
                'total_orders' => $orderQuery->count(),
                'total_amount' => $orderQuery->sum('grand_total'),
                'pending_orders' => $orderQuery->where('status', 'pending')->count(),
                'confirmed_orders' => $orderQuery->where('status', 'confirmed')->count(),
                'converted_orders' => $orderQuery->where('status', 'converted')->count(),
                'cancelled_orders' => $orderQuery->where('status', 'cancelled')->count(),
                'average_order_value' => $orderQuery->avg('grand_total') ?? 0,
            ];

            $data['commission_summary'] = [
                'total_commissions' => \App\Models\AgentCommission::count(),
                'total_commission_amount' => \App\Models\AgentCommission::sum('commission_amount'),
                'pending_commissions' => \App\Models\AgentCommission::where('status', 'pending')->count(),
                'pending_amount' => \App\Models\AgentCommission::where('status', 'pending')->sum('commission_amount'),
                'approved_commissions' => \App\Models\AgentCommission::where('status', 'approved')->count(),
                'approved_amount' => \App\Models\AgentCommission::where('status', 'approved')->sum('commission_amount'),
                'paid_commissions' => \App\Models\AgentCommission::where('status', 'paid')->count(),
                'paid_amount' => \App\Models\AgentCommission::where('status', 'paid')->sum('commission_amount'),
            ];
        }

        // Top Products
        $topProductsQuery = \App\Models\Product::query()
            ->withCount(['salesInvoiceItem as total_sales'])
            ->withSum(['salesInvoiceItem as total_revenue'], 'amount')
            ->orderBy('total_sales', 'desc')
            ->limit(5);

        if ($companyId) {
            $topProductsQuery->where('company_id', $companyId);
        }

        $data['top_products'] = $topProductsQuery->get();

        // Recent Orders
        $recentOrdersQuery = \App\Models\SalesOrder::with(['buyer', 'agent'])
            ->latest()
            ->limit(10);

        if ($companyId) {
            $recentOrdersQuery->where('company_id', $companyId);
        }

        $data['recent_orders'] = $recentOrdersQuery->get();

        return response()->json($data);
    }
}