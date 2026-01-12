<?php

namespace App\Http\Resources\Admin;

use App\Services\InvoiceService;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $outstanding = InvoiceService::getOutstandingInvoicesForBuyer($this->resource);

        $carryForwardInvoices = $outstanding['invoices'];
        $carryForwardTotal = $outstanding['total'];
        $currentPending = $this->pending_amount ?? max(0, ($this->grand_total ?? 0) - ($this->total_paid ?? 0));

        $data['carry_forward_invoices'] = $carryForwardInvoices instanceof \Illuminate\Support\Collection
            ? $carryForwardInvoices->toArray()
            : $carryForwardInvoices;
        $data['carry_forward_total'] = (float) $carryForwardTotal;
        $data['total_payment_due'] = (float) ($currentPending + $carryForwardTotal);

        return $data;
    }
}
