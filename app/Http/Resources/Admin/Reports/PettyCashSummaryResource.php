<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

class PettyCashSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySymbol = '$';

        $total = $this->amount;

        return [
            'id' => $this->id,
            'account_name' => $this->account ? $this->account->name : null,
            'total' => $total,
            'total_label' => $currencySymbol.number_format($total, 2),
        ];
    }
}
