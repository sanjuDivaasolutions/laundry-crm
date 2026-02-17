<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportSummarySheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected array $data,
        protected string $reportType
    ) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function array(): array
    {
        return [
            ['Period', $this->data['period'] ?? $this->reportType],
            ['Start Date', $this->data['start_date'] ?? ($this->data['date'] ?? '-')],
            ['End Date', $this->data['end_date'] ?? ($this->data['date'] ?? '-')],
            ['Total Orders', $this->data['total_orders'] ?? 0],
            ['Total Items', $this->data['total_items'] ?? 0],
            ['Total Revenue', number_format((float) ($this->data['total_revenue'] ?? 0), 2)],
            ['Total Collected', number_format((float) ($this->data['total_collected'] ?? 0), 2)],
            ['Outstanding', number_format((float) ($this->data['outstanding_amount'] ?? ($this->data['pending_amount'] ?? 0)), 2)],
            ['Average Order Value', number_format((float) ($this->data['average_order_value'] ?? 0), 2)],
            ['Urgent Orders', $this->data['urgent_orders'] ?? 0],
            ['New Customers', $this->data['new_customers'] ?? 0],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
