<?php

/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 07/01/25, 5:29 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderListResource extends JsonResource
{
    public function toArray($request)
    {
        $currency = config('system.defaults.currency.symbol');
        $subTotal = number_format($this->sub_total, 2);
        $taxTotal = number_format($this->tax_total, 2);
        $grandTotal = number_format($this->grand_total, 2);

        return [
            'id' => $this->id,
            'so_number' => $this->so_number,
            'date' => $this->date,
            'buyer' => $this->whenLoaded('buyer'),
            'company' => $this->whenLoaded('company'),
            'user' => $this->whenLoaded('user'),
            'sub_total' => $this->sub_total,
            'sub_total_text' => $currency.' '.$subTotal,
            'tax_total' => $this->tax_total,
            'tax_total_text' => $currency.' '.$taxTotal,
            'grand_total' => $this->grand_total,
            'grand_total_text' => $currency.' '.$grandTotal,
        ];
    }
}
