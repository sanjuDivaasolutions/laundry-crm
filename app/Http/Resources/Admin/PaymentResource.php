<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{

    public function toArray($request)
    {

        $currency = config('system.defaults.currency.symbol');

        return [
            'id'                 => $this->id,
            'type'               => $this->type,
            'type_label'         => $this->type_label,
            'amount'             => number_format($this->amount, 2),
            'amount_text'        => $currency . number_format($this->amount, 2),
            'sales_invoice'      => $this->whenLoaded('salesInvoice', $this->salesInvoice ? $this->salesInvoice->invoice_number : null),
            'purchase_invoice'   => $this->whenLoaded('purchaseInvoice', $this->purchaseInvoice ? $this->purchaseInvoice->invoice_number : null),
            'sales_order'        => $this->whenLoaded('salesOrder', $this->salesOrder ? $this->salesOrder->so_number : null),
            'purchase_order'     => $this->whenLoaded('purchaseOrder', $this->purchaseOrder ? $this->purchaseOrder->po_number : null),
            'payment_mode'       => $this->whenLoaded('paymentMode', $this->paymentMode),
            'date'               => $this->date,
            'user'               => $this->whenLoaded('user', $this->user ? $this->user->name : null),
            'payment_type'       => $this->payment_type,
            'payment_type_label' => $this->payment_type_label,
            'remarks'            => $this->remarks,
            'payment_date'       => $this->payment_date,
        ];
    }
}
