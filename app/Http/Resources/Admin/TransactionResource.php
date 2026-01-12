<?php

namespace App\Http\Resources\Admin;

use App\Models\Currency;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        $account = $this->whenLoaded('account', function () {
            return $this->account;
        });
        $showInUsd = $account ? $account->has_multi_currency_transaction : false;
        $balance = $this->balance;

        $primaryCurrency = Config::get('primary_currency');
        if (! $primaryCurrency) {
            $primaryCurrency = Currency::find(config('system.defaults.currency.id', 1));
            Config::set('primary_currency', $primaryCurrency);
        }

        if ($showInUsd) {
            $currency = $primaryCurrency;
        } else {
            $currency = $this->whenLoaded('currency', $this->currency, $primaryCurrency);
        }

        $user = $this->whenLoaded('user', function () {
            return $this->user;
        });

        $url = $this->getReferenceUrl($this->type);
        $route = $this->getReferenceRoute($this->type);

        return [
            'id' => $this->id,
            'account' => $account,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'reference' => $this->reference,
            'reference_link' => $url,
            'reference_route' => $route,
            'date' => $this->date,
            'description' => $this->description,
            'credit' => $this->credit,
            'debit' => $this->debit,
            'balance' => $balance,
            'converted_credit' => $this->converted_credit,
            'converted_debit' => $this->converted_debit,
            'converted_balance' => $this->converted_balance,
            'converted_credit_label' => $this->converted_credit_label,
            'converted_debit_label' => $this->converted_debit_label,
            'converted_balance_label' => $this->converted_balance_label,
            'currency_rate' => $this->currency_rate,
            'currency' => $currency,
            'user' => $user,
        ];
    }

    private function getReferenceUrl($type)
    {
        // Types are pi, si, yi, journal, petty
        return match ($type) {
            'pi' => 'purchase-invoices/show/'.$this->reference.'/overview',
            'si' => 'sales-invoices/show/'.$this->reference.'/overview',
            'yi' => 'yardage-invoices/show/'.$this->reference.'/overview',
            'journal' => null,
            'petty' => null,
            default => null,
        };
    }

    private function getReferenceRoute($type)
    {
        return match ($type) {
            'pi' => 'purchase-invoices.show',
            'si' => 'sales-invoices.show',
            'yi' => 'yardage-invoices.show',
            'journal' => null,
            'petty' => null,
            default => null,
        };
    }
}
