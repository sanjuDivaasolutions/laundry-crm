<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Permission */
class SalesByDepartmentResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySymbol = $this->whenLoaded('currency', function () {
            return $this->currency->symbol;
        });

        return [
            'id' => $this->id,
            'name' => $this->department->name,
            'total' => $this->grand_total,
            'total_label' => $currencySymbol.number_format($this->grand_total, 2),
        ];
    }
}
