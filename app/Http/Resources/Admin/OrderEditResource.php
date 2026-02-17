<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderEditResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'promised_date' => $this->promised_date?->format('Y-m-d'),
            'urgent' => $this->urgent,
            'notes' => $this->notes,
            'hanger_number' => $this->hanger_number,
            'discount_type' => $this->discount_type,
            'discount_amount' => $this->discount_amount,
            'tax_rate' => $this->tax_rate,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'tip_amount' => $this->tip_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'balance_amount' => $this->balance_amount,
            'items' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->map(fn ($item) => [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'service_id' => $item->service_id,
                    'item_name' => $item->item_name,
                    'service_name' => $item->service_name,
                    'pricing_type' => $item->pricing_type ?? 'piece',
                    'weight' => $item->weight,
                    'weight_unit' => $item->weight_unit ?? 'lb',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'color' => $item->color,
                    'brand' => $item->brand,
                    'defect_notes' => $item->defect_notes,
                    'notes' => $item->notes,
                ]);
            }),
        ];
    }
}
