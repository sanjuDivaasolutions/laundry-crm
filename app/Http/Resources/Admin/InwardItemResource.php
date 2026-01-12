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
 *  *  Last modified: 09/01/25, 7:10 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\InwardItem;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InwardItem */
class InwardItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'description' => $this->description,
            'rate' => $this->rate,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'inward_id' => $this->inward_id,
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,

            'product' => new ProductResource($this->whenLoaded('product')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'shelf' => $this->shelf ?? null,
        ];
    }
}
