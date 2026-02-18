<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'service_id' => $this->service_id,
            'item_name' => $this->item_name,
            'service_name' => $this->service_name,
            'pricing_type' => $this->pricing_type,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'barcode' => $this->barcode,
            'color' => $this->color,
            'brand' => $this->brand,
            'notes' => $this->notes,
        ];
    }
}
