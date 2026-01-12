<?php

namespace App\Http\Resources\Admin;

use App\Models\Company;
use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class SampleOrderShowResource extends JsonResource
{
    public function toArray($request)
    {
        $company = Company::first();

        $currency = $this->whenLoaded('currency', $this->currency, Currency::find(config('system.defaults.currency.id')));

        return [
            'id' => $this->id,
            'code' => $this->code,
            'company' => $this->whenLoaded('company', $this->company, $company),
            'department' => $this->whenLoaded('department', $this->department),
            'currency' => $currency,
            'currency_rate' => $this->whenLoaded('currencyRate', $this->currency_rate),
            'buyer_id' => $this->buyer_id,
            'buyer' => $this->buyer,
            'name' => $this->name,
            'date' => $this->date,
            'contact_person' => $this->contact_person,
            'sub_total' => number_format($this->sub_total, 2),
            'sub_total_label' => $currency->symbol.number_format($this->sub_total, 2),
            'expense_total' => number_format($this->expense_total, 2),
            'expense_total_label' => $currency->symbol.number_format($this->expense_total, 2),
            'discount_total' => number_format($this->discount_total, 2),
            'discount_total_label' => $currency->symbol.number_format($this->discount_total, 2),
            'grand_total' => number_format($this->grand_total, 2),
            'grand_total_label' => $currency->symbol.number_format($this->grand_total, 2),
            'items' => $this->whenLoaded('items', SampleOrderItemShowResource::collection($this->items)),
        ];
    }
}
