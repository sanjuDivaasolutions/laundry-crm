<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Customer::query()->withCount('orders');

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', (bool) $this->filters['is_active']);
        }
        if (! empty($this->filters['loyalty_tier'])) {
            $query->where('loyalty_tier', $this->filters['loyalty_tier']);
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Phone',
            'Address',
            'Status',
            'Total Orders',
            'Total Spent',
            'Loyalty Points',
            'Loyalty Tier',
            'Created At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->customer_code,
            $customer->name,
            $customer->phone,
            $customer->address ?? '-',
            $customer->is_active ? 'Active' : 'Inactive',
            $customer->orders_count,
            number_format((float) $customer->total_spent, 2),
            $customer->loyalty_points,
            ucfirst($customer->loyalty_tier ?? 'bronze'),
            $customer->created_at?->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
