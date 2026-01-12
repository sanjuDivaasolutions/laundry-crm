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
 *  *  Last modified: 12/12/24, 6:12 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\Inward;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Inward */
class InwardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'reference_no'   => $this->reference_no,
            'date'           => $this->date,
            'remark'         => $this->remark,
            'currency_rate'  => $this->currency_rate,
            'sub_total'      => $this->sub_total,
            'tax_total'      => $this->tax_total,
            'tax_rate'       => $this->tax_rate,
            'grand_total'    => $this->grand_total,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,

            'company_id'   => $this->company_id,
            'supplier_id'  => $this->supplier_id,
            'warehouse_id' => $this->warehouse_id,
            'user_id'      => $this->user_id,

            'items' => InwardItemResource::collection($this->whenLoaded('items')),

            'company'   => new CompanyResource($this->whenLoaded('company')),
            'supplier'  => new SupplierResource($this->whenLoaded('supplier')),
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'state'     => $this->whenLoaded('state'),
            'taxes'     => $this->whenLoaded('taxes'),
        ];
    }
}
