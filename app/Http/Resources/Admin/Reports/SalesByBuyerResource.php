<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Permission */
class SalesByBuyerResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySymbol = $this->whenLoaded('currency', function() {
            return $this->currency->symbol;
        });

        return [
            'id'            => $this->id,
            'code'          => $this->buyer->code,
            'name'          => $this->buyer->name,
            'display_name'  => $this->buyer->display_name,
            'total'         => $this->grand_total,
            'total_label'   => $currencySymbol . number_format($this->grand_total,2),
        ];
    }
}
