<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerEditResource extends JsonResource
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
            'loyalty_points' => $this->loyalty_points,
            'loyalty_tier' => $this->loyalty_tier,
            'total_orders_count' => $this->total_orders_count,
            'total_spent' => $this->total_spent,
        ];
    }
}
