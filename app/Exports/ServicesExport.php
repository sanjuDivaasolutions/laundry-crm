<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Service::query();

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
            'Display Order',
            'Status',
            'Description',
        ];
    }

    public function map($service): array
    {
        return [
            $service->code,
            $service->name,
            $service->display_order,
            $service->is_active ? 'Active' : 'Inactive',
            $service->description ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
