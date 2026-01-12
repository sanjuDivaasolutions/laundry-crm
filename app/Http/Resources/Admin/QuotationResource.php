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
 *  *  Last modified: 21/01/25, 4:27 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\Quotation;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quotation */
class QuotationResource extends JsonResource
{
    public function toArray($request)
    {
        /*return [
            'id'                     => $this->id,
            'order_no'               => $this->order_no,
            'reference_no'           => $this->reference_no,
            'order_date'             => $this->order_date,
            'sub_total'              => $this->sub_total,
            'tax_total'              => $this->tax_total,
            'grand_total'            => $this->grand_total,
            'remark'                 => $this->remark,
            'expected_delivery_date' => $this->expected_delivery_date,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,

            'buyer_id'     => $this->buyer_id,
            'warehouse_id' => $this->warehouse_id,
            'state_id'     => $this->state_id,
        ];*/

        return parent::toArray($request);
    }
}
