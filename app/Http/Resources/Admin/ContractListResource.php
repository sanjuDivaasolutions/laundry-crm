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
 *  *  Last modified: 17/10/24, 6:56 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractListResource extends JsonResource
{
    public function toArray($request)
    {
        $revision = $this->whenLoaded('revision');
        $currencySign = '$';
        $amount = $revision ? $revision->sub_total : 0;

        $amount_label = $currencySign.number_format($amount, 2);

        return [
            'id' => $this->id,
            'code' => $this->code,
            'contract_type' => $revision?->contract_type,
            'buyer' => $this->whenLoaded('buyer'),
            'description' => $this->description,
            'start_date' => $revision?->start_date,
            'end_date' => $revision?->end_date,
            'date' => $this->date,
            'installment_count' => $this->installment_count,
            'installment_remaining' => $this->installment_count,
            'amount' => $amount,
            'amount_label' => $amount_label,
        ];
    }
}
