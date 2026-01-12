<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractInvoiceListResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySign = '$';

        $outstanding_total = 0;
        $payments = $this->whenLoaded('payments', []);
        if ($payments) {
            foreach ($payments as $payment) {
                $outstanding_total += $payment->amount;
            }
        }

        return [
            'id'                     => $this->id,
            'invoice_number'         => $this->invoice_number,
            'client'                 => $this->whenLoaded('client'),
            'items'                  => $this->whenLoaded('items'),
            'date'                   => $this->date,
            'sub_total'              => $this->sub_total,
            'sub_total_text'         => $currencySign . number_format($this->sub_total, 2),
            'tax_total'              => $this->tax_total,
            'tax_total_text'         => $currencySign . number_format($this->tax_total, 2),
            'grand_total'            => $this->grand_total,
            'grand_total_text'       => $currencySign . number_format($this->grand_total, 2),
            'outstanding_total'      => $outstanding_total,
            'outstanding_total_text' => $currencySign . number_format($outstanding_total, 2),
            'tax_rate'               => $this->tax_rate,
            'tax_rate_text'          => $this->tax_rate . '%',
            'remark'                 => $this->remark,
            'description'            => $this->description,
            'status'                 => $this->status,
            'status_label'           => $this->status_label,
            'payment_status'         => $this->payment_status,
            'payment_status_label'   => $this->payment_status_label,
            'pdf_url'                => $this->stripe_pdf_url,
            'pdf_url_label'          => 'PDF',
            'invoice_url'            => $this->stripe_invoice_url,
            'invoice_url_label'      => 'View Invoice',
            'payments'               => $payments,
        ];
    }
}
