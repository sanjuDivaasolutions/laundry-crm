<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin \App\Models\Permission */
class SalesByMonthResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySymbol = '$';

        $month = Carbon::createFromFormat('Y-m', $this->month)->format('F Y');

        return [
            'month' => $month,
            'total' => $this->total,
            'total_label' => $currencySymbol.number_format($this->total, 2),
        ];
    }
}
