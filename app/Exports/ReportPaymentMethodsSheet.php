<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportPaymentMethodsSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Payment Methods';
    }

    public function headings(): array
    {
        return ['Method', 'Count', 'Total'];
    }

    public function array(): array
    {
        return array_map(fn ($item) => [
            ucfirst($item['method']),
            $item['count'],
            number_format((float) $item['total'], 2),
        ], $this->data);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 12]]];
    }
}
