<?php

namespace App\Http\Resources\Admin;

use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class SampleOrderItemShowResource extends JsonResource
{
    public function toArray($request)
    {
        $sampleOrder = $this->whenLoaded('sampleOrder', $this->sampleOrder, null);
        $currency = ($sampleOrder) ? $sampleOrder->currency : Currency::find(config('system.defaults.currency.id'));

        return [
            'id' => $this->id,
            'inquiry_id' => $this->inquiry_id,
            'product' => $this->product,
            'quantity' => $this->quantity,
            'rate' => number_format($this->rate, 2),
            'amount' => number_format($this->amount, 2),
            'rate_label' => $currency->symbol.number_format($this->rate, 2),
            'amount_label' => $currency->symbol.number_format($this->amount, 2),
            'type' => $this->type_label,
            'unit' => $this->whenLoaded('unit', $this->unit),
            'sales_order' => $this->whenLoaded('salesOrder', $this->salesOrder),
            'sales_order_item' => $this->whenLoaded('salesOrderItem', $this->salesOrderItem),
        ];
    }
}
