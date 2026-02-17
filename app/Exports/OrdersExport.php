<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function query()
    {
        $query = Order::query()->with(['customer', 'processingStatus', 'orderStatus']);

        if (! empty($this->filters['start_date'])) {
            $query->where('order_date', '>=', $this->filters['start_date']);
        }
        if (! empty($this->filters['end_date'])) {
            $query->where('order_date', '<=', $this->filters['end_date']);
        }
        if (! empty($this->filters['payment_status'])) {
            $query->where('payment_status', $this->filters['payment_status']);
        }
        if (! empty($this->filters['processing_status_id'])) {
            $query->where('processing_status_id', $this->filters['processing_status_id']);
        }
        if (isset($this->filters['urgent'])) {
            $query->where('urgent', (bool) $this->filters['urgent']);
        }

        return $query->orderByDesc('order_date');
    }

    public function headings(): array
    {
        return [
            'Order #',
            'Customer',
            'Phone',
            'Order Date',
            'Promised Date',
            'Items',
            'Subtotal',
            'Discount',
            'Tax',
            'Total',
            'Paid',
            'Balance',
            'Payment Status',
            'Processing Status',
            'Urgent',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->customer?->name ?? '-',
            $order->customer?->phone ?? '-',
            $order->order_date?->format('Y-m-d'),
            $order->promised_date?->format('Y-m-d'),
            $order->total_items,
            number_format((float) $order->subtotal, 2),
            number_format((float) $order->discount_amount, 2),
            number_format((float) $order->tax_amount, 2),
            number_format((float) $order->total_amount, 2),
            number_format((float) $order->paid_amount, 2),
            number_format((float) $order->balance_amount, 2),
            $order->payment_status ?? '-',
            $order->processingStatus?->status_name ?? '-',
            $order->urgent ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
