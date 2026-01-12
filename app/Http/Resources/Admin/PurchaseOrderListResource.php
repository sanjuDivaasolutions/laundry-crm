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
 *  *  Last modified: 16/10/24, 5:37 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderListResource extends JsonResource
{
    public function toArray($request)
    {
        $currency = config('system.defaults.currency.symbol');
        $subTotal = number_format($this->sub_total, 2);
        $taxTotal = number_format($this->tax_total, 2);
        $grandTotal = number_format($this->grand_total, 2);

        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'date' => $this->date,
            'company' => $this->whenLoaded('company'),
            'supplier' => $this->whenLoaded('supplier'),
            'user' => $this->whenLoaded('user'),
            'sub_total' => $this->sub_total,
            'sub_total_label' => $currency.' '.$subTotal,
            'tax_total' => $this->tax_total,
            'tax_total_label' => $currency.' '.$taxTotal,
            'grand_total' => $this->grand_total,
            'grand_total_label' => $currency.' '.$grandTotal,
        ];
    }
}
