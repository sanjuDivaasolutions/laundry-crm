<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class BuyerResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['shipping_same_as_billing'] = (bool) ($this->billing_address_id && $this->billing_address_id === $this->shipping_address_id);
        $data['agent_name'] = $this->agent_name;
        $data['commission_rate'] = $this->commission_rate;
        $data['agent_id'] = $this->agent_id;
        $data['agent'] = $this->whenLoaded('agent', function () {
            return [
                'id' => $this->agent->id,
                'name' => $this->agent->name,
                'display_name' => $this->agent->display_name,
                'is_agent' => (bool) $this->agent->is_agent,
            ];
        });

        return $data;
    }
}
