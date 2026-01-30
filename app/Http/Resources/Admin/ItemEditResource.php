<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemEditResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'price' => $this->price,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'service_prices' => $this->whenLoaded('servicePrices', function () {
                return $this->servicePrices->map(fn ($sp) => [
                    'service_id' => $sp->service_id,
                    'price' => $sp->price,
                    'is_active' => $sp->is_active,
                ]);
            }),
        ];
    }
}
