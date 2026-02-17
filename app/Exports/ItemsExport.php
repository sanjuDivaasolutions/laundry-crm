<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Item::query();

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', (bool) $this->filters['is_active']);
        }

        return $query->orderBy('display_order');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Price',
            'Display Order',
            'Status',
            'Description',
        ];
    }

    public function map($item): array
    {
        return [
            $item->code,
            $item->name,
            number_format((float) $item->price, 2),
            $item->display_order,
            $item->is_active ? 'Active' : 'Inactive',
            $item->description ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
