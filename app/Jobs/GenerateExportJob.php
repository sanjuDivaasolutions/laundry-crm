<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exports\CustomersExport;
use App\Exports\DeliverySchedulesExport;
use App\Exports\ItemsExport;
use App\Exports\OrdersExport;
use App\Exports\ReportExport;
use App\Exports\ServicesExport;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class GenerateExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        protected int $userId,
        protected string $module,
        protected string $format,
        protected array $filters = [],
        protected ?array $reportData = null
    ) {}

    public function handle(): void
    {
        $export = $this->resolveExport();
        $fileName = $this->generateFileName();
        $storagePath = "exports/{$fileName}";

        if ($this->format === 'pdf') {
            Excel::store($export, $storagePath, 'local', \Maatwebsite\Excel\Excel::MPDF);
        } else {
            Excel::store($export, $storagePath, 'local', \Maatwebsite\Excel\Excel::XLSX);
        }

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ExportReadyNotification(
                module: $this->module,
                fileName: $fileName,
                format: $this->format,
                downloadPath: $storagePath
            ));
        }
    }

    protected function resolveExport(): object
    {
        return match ($this->module) {
            'orders' => new OrdersExport($this->filters),
            'customers' => new CustomersExport($this->filters),
            'items' => new ItemsExport($this->filters),
            'services' => new ServicesExport($this->filters),
            'deliveries' => new DeliverySchedulesExport($this->filters),
            'reports' => new ReportExport($this->reportData ?? [], $this->filters['report_type'] ?? 'monthly'),
            default => throw new \InvalidArgumentException("Unknown export module: {$this->module}"),
        };
    }

    protected function generateFileName(): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $ext = $this->format === 'pdf' ? 'pdf' : 'xlsx';

        return "{$this->module}_{$timestamp}.{$ext}";
    }
}
