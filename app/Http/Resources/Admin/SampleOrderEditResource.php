<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SampleOrderEditResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'buyer_id' => $this->buyer_id,
            'buyer' => $this->whenLoaded('buyer', $this->buyer, null),
            'company' => $this->whenLoaded('company', $this->company, null),
            'currency' => $this->whenLoaded('currency', $this->currency, null),
            'department' => $this->whenLoaded('department', $this->department, null),
            'inquiry_source' => $this->whenLoaded('inquirySource', $this->inquirySource, null),
            'inquiry_source_id' => $this->inquiry_source_id,
            'currency_rate' => $this->currency_rate,
            'date' => $this->date,
            'contact_person' => $this->contact_person,
            'description' => $this->description,
            'remark' => $this->remark,
            'courier_number' => $this->courier_number,
            'sample_cost' => $this->sample_cost,
            'courier_cost' => $this->courier_cost,
            'sample_cost_refund' => $this->sample_cost_refund,
            'courier_cost_refundable' => $this->courier_cost_refundable,
            'items' => $this->whenLoaded('items', SampleOrderItemEditResource::collection($this->items)),
            'image' => $this->image,
        ];
    }
}
