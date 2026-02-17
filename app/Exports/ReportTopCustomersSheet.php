<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportTopCustomersSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Top Customers';
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Orders', 'Total Spent'];
    }

    public function array(): array
    {
        return array_map(fn ($item) => [
            $item['name'],
            $item['phone'],
            $item['order_count'],
            number_format((float) $item['total_spent'], 2),
        ], $this->data);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 12]]];
    }
}
