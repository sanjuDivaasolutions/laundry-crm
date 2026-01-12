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
 *  *  Last modified: 21/01/25, 9:55 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\QuotationStatus;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuotationStatus */
class QuotationStatusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'active' => $this->active,
            'status' => $this->status,
            'remark' => $this->remark,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'quotation_id' => $this->quotation_id,
            'user_id' => $this->user_id,

            'quotation' => new QuotationResource($this->whenLoaded('quotation')),
        ];
    }
}
