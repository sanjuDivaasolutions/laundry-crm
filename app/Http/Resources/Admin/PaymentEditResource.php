<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentEditResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'payment_date'          => $this->payment_date,
            'amount'                => $this->amount,
            'remarks'               => $this->remarks,
            'tran_type'             => $this->tran_type,
            'payment_type'          => $this->payment_type,
            'payment_mode_id'       => $this->payment_mode_id,
            'order_no'              => $this->order_no,
            'user_id'               => $this->user_id,
            'sales_invoice_id'      => $this->sales_invoice_id,
            'sales_order_id'        => $this->sales_order_id,
            'purchase_invoice_id'   => $this->purchase_invoice_id,
            'purchase_order_id'     => $this->purchase_order_id,
            
            // Include relationships for display
            'payment_mode'          => $this->whenLoaded('paymentMode'),
            'sales_invoice'         => $this->whenLoaded('salesInvoice'),
            'purchase_invoice'      => $this->whenLoaded('purchaseInvoice'),
            'sales_order'           => $this->whenLoaded('salesOrder'),
            'purchase_order'        => $this->whenLoaded('purchaseOrder'),
            'user'                  => $this->whenLoaded('user'),
        ];
    }
}