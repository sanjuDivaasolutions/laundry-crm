<?php

namespace App\Http\Resources\Admin;

use App\Models\PurchaseOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderEditResource extends JsonResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        if($this->shipping_sample_required) {
            $shipping_sample_required = collect(PurchaseOrder::SHIPPING_SAMPLE_REQUIRED_SELECT)->firstWhere('value', $this->shipping_sample_required);
            $result['shipping_sample_required'] = [
                'value' => $shipping_sample_required['value'],
                'label' => $shipping_sample_required['label'],
            ];
        }

        return $result;
    }
}
