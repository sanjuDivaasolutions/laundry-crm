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
 *  *  Last modified: 05/02/25, 6:07 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\InventoryAdjustment;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InventoryAdjustment */
class InventoryAdjustmentResource extends JsonResource
{
    public function toArray($request)
    {
        $reason = ! is_array($this->reason) ? collect(InventoryAdjustment::REASON_SELECT)->firstWhere('value', $this->reason) : $this->reason;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'date' => $this->date,
            'reason' => $reason,
            'reason_label' => is_array($reason) ? $reason['label'] : $reason,
            'remark' => $this->remark,
            'adjusted_quantity' => $this->adjusted_quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'product' => $this->whenLoaded('product'),
            'shelf' => $this->whenLoaded('shelf'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
