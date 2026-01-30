<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'service_name' => $this->service_name,
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
