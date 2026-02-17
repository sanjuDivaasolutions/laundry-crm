<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportExport implements WithMultipleSheets
{
    public function __construct(
        protected array $reportData,
        protected string $reportType = 'monthly'
    ) {}

    public function sheets(): array
    {
        $sheets = [
            new ReportSummarySheet($this->reportData, $this->reportType),
        ];

        if (! empty($this->reportData['top_services'])) {
            $sheets[] = new ReportTopServicesSheet($this->reportData['top_services']);
        }
        if (! empty($this->reportData['top_customers'])) {
            $sheets[] = new ReportTopCustomersSheet($this->reportData['top_customers']);
        }
        if (! empty($this->reportData['payment_methods'])) {
            $sheets[] = new ReportPaymentMethodsSheet($this->reportData['payment_methods']);
        }
        if (! empty($this->reportData['status_distribution'])) {
            $sheets[] = new ReportStatusDistributionSheet($this->reportData['status_distribution']);
        }

        return $sheets;
    }
}
