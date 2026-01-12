<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Permission */
class SalesByProductResource extends JsonResource
{
    public function toArray($request)
    {
        $totalQuantity = $this->whenLoaded('salesInvoiceItem', function () {
            return $this->salesInvoiceItem->sum('quantity');
        });

        $currencySymbol = '$';
        $grandTotal = 0;
        if ($this->salesInvoiceItem) {
            $grandTotal = $this->salesInvoiceItem->sum(function ($q) {
                return $q->rate * $q->quantity;
            });
        }

        return [
            'id'          => $this->id,
            'sku'         => $this->sku,
            'name'        => $this->name,
            'quantity'    => $totalQuantity ?? 0,
            'total'       => $grandTotal,
            'total_label' => $currencySymbol . number_format($grandTotal, 2),
        ];
    }
}
