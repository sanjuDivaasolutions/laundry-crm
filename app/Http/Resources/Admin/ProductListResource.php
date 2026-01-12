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
 *  *  Last modified: 12/12/24, 7:03 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductListResource extends JsonResource
{
    public function toArray($request)
    {
        $stock = new ProductStockResourceCollection($this->whenLoaded('stock'));

        $onHand = $stock->sum('on_hand');
        $inTransit = $stock->sum('in_transit');

        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'active' => $this->active,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'on_hand' => $onHand,
            'in_transit' => $inTransit,
            'on_hand_label' => $onHand,
            'in_transit_label' => $inTransit,
        ];
    }
}
