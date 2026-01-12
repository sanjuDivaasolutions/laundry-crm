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
 *  *  Last modified: 12/12/24, 6:58 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\ProductStock */
class ProductStockResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $data = ProductStockResource::collection($this->collection);

        return [
            'stock'      => $data,
            'on_hand'    => $data->sum('on_hand'),
            'in_transit' => $data->sum('in_transit'),
        ];
    }
}
