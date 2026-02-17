<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\DeliverySchedule;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeliverySchedulesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = DeliverySchedule::query()->with(['order', 'customer']);

        if (! empty($this->filters['start_date'])) {
            $query->where('scheduled_date', '>=', $this->filters['start_date']);
        }
        if (! empty($this->filters['end_date'])) {
            $query->where('scheduled_date', '<=', $this->filters['end_date']);
        }
        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderByDesc('scheduled_date');
    }

    public function headings(): array
    {
        return [
            'Order #',
            'Customer',
            'Type',
            'Scheduled Date',
            'Scheduled Time',
            'Address',
            'Status',
            'Notes',
            'Completed At',
        ];
    }

    public function map($delivery): array
    {
        return [
            $delivery->order?->order_number ?? '-',
            $delivery->customer?->name ?? '-',
            ucfirst($delivery->type),
            $delivery->scheduled_date?->format('Y-m-d'),
            $delivery->scheduled_time,
            $delivery->address ?? '-',
            ucfirst($delivery->status),
            $delivery->notes ?? '-',
            $delivery->completed_at?->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
