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
 *  *  Last modified: 16/10/24, 5:23 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdatePurchaseOrderRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('purchase_order_edit');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('supplier');
        $this->setObjectId('warehouse');
        $this->setObjectId('payment_term');
        $this->setObjectId('shipment_mode');
        $this->setObjectId('company');
    }

    public function rules()
    {
        return [
            'company_id'              => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'po_number'               => [
                'string',
                'required',
            ],
            'date'                    => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'estimated_shipment_date' => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'supplier_id'             => [
                'integer',
                'exists:suppliers,id',
                'nullable',
            ],
            'payment_term_id'         => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'shipment_mode_id'        => [
                'integer',
                'exists:shipment_modes,id',
                'nullable',
            ],
            'warehouse_id'            => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
            'remarks'                 => [
                'string',
                'nullable',
            ],
            'user_id'                 => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
            'freight_total'           => [
                'numeric',
                'nullable',
            ],
            'discount_type'           => [
                'nullable',
                'in:' . implode(',', Arr::pluck(PurchaseOrder::DISCOUNT_TYPE_SELECT, 'value')),
            ],
            'discount_total'          => [
                'numeric',
                'nullable',
            ],
            'discount_rate'           => [
                'numeric',
                'nullable',
            ],
            'sub_total'               => [
                'numeric',
                'required',
            ],
            'tax_rate'                => [
                'numeric',
                'required',
            ],
            'tax_total'               => [
                'numeric',
                'required',
            ],
            'grand_total'             => [
                'numeric',
                'required',
            ],
        ];
    }
}
