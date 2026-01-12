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
 *  *  Last modified: 17/01/25, 8:01 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\QuotationItem;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuotationItem */
class QuotationItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'sku'        => $this->sku,
            'rate'       => $this->rate,
            'quantity'   => $this->quantity,
            'amount'     => $this->amount,
            'remark'     => $this->remark,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'quotation_id' => $this->quotation_id,
            'product_id'   => $this->product_id,
            'unit_id'      => $this->unit_id,

            'quotation' => new QuotationResource($this->whenLoaded('quotation')),
            'product'   => new ProductListResource($this->whenLoaded('product')),
        ];
    }
}
