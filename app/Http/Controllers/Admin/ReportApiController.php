<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ReportApiController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Get daily report.
     *
     * GET /api/v1/reports/daily?date=2026-02-13
     */
    public function daily(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $date = $request->query('date');

        return $this->success($this->reportService->getDailySummary($date));
    }

    /**
     * Get weekly report.
     *
     * GET /api/v1/reports/weekly?start_date=2026-02-10
     */
    public function weekly(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $startDate = $request->query('start_date');

        return $this->success($this->reportService->getWeeklySummary($startDate));
    }

    /**
     * Get monthly report.
     *
     * GET /api/v1/reports/monthly?month=2026-02
     */
    public function monthly(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $month = $request->query('month');

        return $this->success($this->reportService->getMonthlySummary($month));
    }

    /**
     * Get revenue trend for a date range.
     *
     * GET /api/v1/reports/revenue-trend?start_date=2026-02-01&end_date=2026-02-13
     */
    public function revenueTrend(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        return $this->success($this->reportService->getRevenueTrend(
            $request->query('start_date'),
            $request->query('end_date')
        ));
    }

    /**
     * Get top services report.
     *
     * GET /api/v1/reports/top-services?start_date=2026-02-01&end_date=2026-02-13
     */
    public function topServices(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $limit = min((int) $request->query('limit', 10), 50);

        return $this->success($this->reportService->getTopServices(
            $request->query('start_date'),
            $request->query('end_date'),
            $limit
        ));
    }

    /**
     * Get top customers report.
     *
     * GET /api/v1/reports/top-customers?start_date=2026-02-01&end_date=2026-02-13
     */
    public function topCustomers(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $limit = min((int) $request->query('limit', 10), 50);

        return $this->success($this->reportService->getTopCustomers(
            $request->query('start_date'),
            $request->query('end_date'),
            $limit
        ));
    }

    /**
     * Get payment method breakdown.
     *
     * GET /api/v1/reports/payment-methods?start_date=2026-02-01&end_date=2026-02-13
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        return $this->success($this->reportService->getPaymentMethodBreakdown(
            $request->query('start_date'),
            $request->query('end_date')
        ));
    }

    /**
     * Get order status distribution.
     *
     * GET /api/v1/reports/status-distribution?start_date=2026-02-01&end_date=2026-02-13
     */
    public function statusDistribution(Request $request): JsonResponse
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        return $this->success($this->reportService->getStatusDistribution(
            $request->query('start_date'),
            $request->query('end_date')
        ));
    }
}
