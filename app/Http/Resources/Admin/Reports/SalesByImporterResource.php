<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Permission */
class SalesByImporterResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySymbol = '$';

        $grandTotal = 0;
        if($this->salesInvoice) {
            $grandTotal = $this->salesInvoice->sum(function ($q) {
                return $q->grand_total / $q->currency_rate;
            });
        }

        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'display_name'  => $this->display_name,
            'total'         => $grandTotal,
            'total_label'   => $currencySymbol . number_format($grandTotal,2),
        ];
    }
}
