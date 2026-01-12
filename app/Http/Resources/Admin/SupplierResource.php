<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['shipping_same_as_billing'] = (bool) ($this->billing_address_id && $this->billing_address_id === $this->shipping_address_id);
        $data['is_agent'] = (bool) $this->is_agent;

        return $data;
    }
}
