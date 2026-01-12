<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierListResource extends JsonResource
{
    public function toArray($request)
    {
        $account = null;
        $balance = 0;
        $currencySign = '$';
        if ($this->account) {
            $balance = $this->account->balance;
            $currency = $this->account->currency;
            if ($currency) {
                $currencySign = $currency->symbol;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'account' => $account,
            'payment_term' => $this->whenLoaded('paymentTerm', $this->paymentTerm),
            'department' => $this->whenLoaded('department', $this->department),
            'balance' => number_format($balance, 2),
            'balance_text' => $currencySign.number_format($balance, 2),
        ];
    }
}
