<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateExportJob;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ExportApiController extends Controller
{
    private const ALLOWED_MODULES = ['orders', 'customers', 'items', 'services', 'deliveries', 'reports'];

    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Queue export for processing (all exports are queued).
     *
     * POST /api/v1/exports/{module}/{format}
     */
    public function export(Request $request, string $module, string $format): JsonResponse
    {
        $this->authorizeModule($module);
        $this->validateFormat($format);

        $filters = $request->only([
            'start_date', 'end_date', 'payment_status', 'processing_status_id',
            'urgent', 'is_active', 'loyalty_tier', 'type', 'status',
            'report_type', 'month', 'date',
        ]);

        $reportData = null;
        if ($module === 'reports') {
            $reportData = $this->getReportData($filters);
        }

        GenerateExportJob::dispatch(
            userId: auth()->id(),
            module: $module,
            format: $format,
            filters: $filters,
            reportData: $reportData
        );

        return response()->json([
            'message' => ucfirst($module)." {$format} export queued. You will be notified when it's ready.",
        ]);
    }

    /**
     * Download a previously generated export file.
     *
     * GET /api/v1/exports/download/{filename}
     */
    public function download(string $filename): BinaryFileResponse|JsonResponse
    {
        $path = "exports/{$filename}";

        if (! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'File not found or expired.'], Response::HTTP_NOT_FOUND);
        }

        return response()->download(
            Storage::disk('local')->path($path),
            $filename
        )->deleteFileAfterSend(true);
    }

    protected function authorizeModule(string $module): void
    {
        abort_if(! in_array($module, self::ALLOWED_MODULES), Response::HTTP_BAD_REQUEST, 'Invalid module.');

        $gateMap = [
            'orders' => 'order_access',
            'customers' => 'customer_access',
            'items' => 'item_access',
            'services' => 'service_access',
            'deliveries' => 'delivery_access',
            'reports' => 'report_access',
        ];

        abort_if(Gate::denies($gateMap[$module]), Response::HTTP_FORBIDDEN);
    }

    protected function validateFormat(string $format): void
    {
        abort_if(! in_array($format, ['excel', 'pdf']), Response::HTTP_BAD_REQUEST, 'Format must be excel or pdf.');
    }

    protected function getReportData(array $filters): array
    {
        $reportType = $filters['report_type'] ?? 'monthly';

        return match ($reportType) {
            'daily' => $this->reportService->getDailySummary($filters['date'] ?? null),
            'weekly' => $this->reportService->getWeeklySummary($filters['start_date'] ?? null),
            'monthly' => $this->reportService->getMonthlySummary($filters['month'] ?? null),
            default => $this->reportService->getMonthlySummary(),
        };
    }
}
