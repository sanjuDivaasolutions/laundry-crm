<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer_code' => $this->customer_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'recent_orders' => OrderResource::collection($this->whenLoaded('orders')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
