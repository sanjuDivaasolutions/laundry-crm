<?php

namespace App\Http\Resources\Admin;

use App\Models\SampleOrderItem;
use Illuminate\Http\Resources\Json\JsonResource;

class SampleOrderItemEditResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sample_order_id' => $this->sample_order_id,
            'product' => $this->product,
            'type' => collect(SampleOrderItem::TYPE_SELECT)->where('value', $this->type)->first(),
            'quantity' => $this->quantity,
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency', $this->currency),
            'unit' => $this->whenLoaded('unit', $this->unit),
            'rate' => $this->rate,
            'remark' => $this->remark,
            'sales_order' => $this->whenLoaded('salesOrder', $this->salesOrder),
            'sales_order_item' => $this->whenLoaded('salesOrderItem', $this->salesOrderItem),
        ];
    }
}
