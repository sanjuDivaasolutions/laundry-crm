<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 5:39 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ContractShowResource extends JsonResource
{
    public function toArray($request)
    {
        $currencySign = '$';

        $revision = $this->revision;

        $amount = $revision->grand_total;

        $balanceAmount = $revision->grand_total;

        $subscriptionStatus = $revision->subscription ? $revision->subscription->stripe_status : '-';
        $subscriptionStatusLabel = Str::title(str_replace('_', ' ', $subscriptionStatus));

        return [
            'id'                        => $this->id,
            'contract_type'             => $revision->contract_type,
            'code'                      => $this->code,
            'buyer'                     => $this->whenLoaded('buyer'),
            'items'                     => $this->whenLoaded('items'),
            'description'               => $this->description,
            'start_date'                => $revision->start_date,
            'end_date'                  => $revision->end_date,
            'date'                      => $this->date,
            'subscription_status'       => $subscriptionStatus,
            'subscription_status_label' => $subscriptionStatusLabel,
            'installment_count'         => $revision->installment_count,
            'installment_remaining'     => $revision->installment_count,
            'total_amount'              => $revision->grand_total,
            'total_amount_text'         => $currencySign . number_format($amount, 2),
            'balance_amount'            => $balanceAmount,
            'balance_amount_text'       => $currencySign . number_format($balanceAmount, 2),
            'sub_total'                 => $revision->sub_total,
            'sub_total_text'            => $currencySign . number_format($revision->sub_total, 2),
            'tax_total'                 => $revision->tax_total,
            'tax_total_text'            => $currencySign . number_format($revision->tax_total, 2),
            'tax_rate'                  => $revision->tax_rate,
            'tax_rate_text'             => $revision->tax_rate . '%',
            'grand_total'               => $revision->grand_total,
            'grand_total_text'          => $currencySign . number_format($revision->grand_total, 2),
        ];
    }
}
