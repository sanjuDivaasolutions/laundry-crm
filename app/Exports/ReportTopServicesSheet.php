<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportTopServicesSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Top Services';
    }

    public function headings(): array
    {
        return ['Service', 'Orders', 'Quantity', 'Revenue'];
    }

    public function array(): array
    {
        return array_map(fn ($item) => [
            $item['service_name'],
            $item['order_count'],
            $item['total_quantity'],
            number_format((float) $item['total_revenue'], 2),
        ], $this->data);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 12]]];
    }
}
