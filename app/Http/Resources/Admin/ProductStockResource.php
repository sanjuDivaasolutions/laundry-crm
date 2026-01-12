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
 *  *  Last modified: 12/12/24, 6:56 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\ProductStock;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductStock */
class ProductStockResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'on_hand' => $this->on_hand,
            'in_transit' => $this->in_transit,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
        ];
    }
}
