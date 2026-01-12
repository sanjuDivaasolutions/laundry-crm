<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Supplier */
class SalesCommissionResource extends JsonResource
{
    public function toArray($request)
    {
        $totalCommission = $this->whenLoaded('salesInvoicesAsAgent', function () {
            return $this->salesInvoicesAsAgent->sum('commission_total');
        });

        $currencySymbol = '$';

        return [
            'id' => $this->id,
            'agent_name' => $this->name,
            'total_commission' => $totalCommission ?? 0,
            'total_commission_label' => $currencySymbol.number_format($totalCommission ?? 0, 2),
        ];
    }
}
