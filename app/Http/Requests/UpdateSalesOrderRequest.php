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
 *  *  Last modified: 16/10/24, 4:29 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Requests;

use App\Models\SalesOrder;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateSalesOrderRequest extends FormRequest
{
    use CustomFormRequest;

    public function prepareForValidation()
    {
        $this->setObjectId('buyer');
        $this->setObjectId('payment_term');
        $this->setObjectId('warehouse');
        $this->setObjectId('company');
    }

    public function authorize()
    {
        return Gate::allows('sales_order_edit');
    }

    public function rules()
    {
        return [
            'company_id' => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'so_number' => [
                'string',
                'required',
            ],
            'quotation_no' => [
                'string',
                'nullable',
            ],
            'reference_no' => [
                'string',
                'nullable',
            ],
            'warehouse_id' => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
            'type' => [
                'required',
                'in:'.implode(',', Arr::pluck(SalesOrder::TYPE_SELECT, 'value')),
            ],
            'date' => [
                'date_format:'.config('project.date_format'),
                'required',
            ],
            'estimated_shipment_date' => [
                'date_format:'.config('project.date_format'),
                'nullable',
            ],
            'buyer_id' => [
                'integer',
                'exists:buyers,id',
                'nullable',
            ],
            'payment_term_id' => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'remarks' => [
                'string',
                'nullable',
            ],
            'sub_total' => [
                'numeric',
                'required',
            ],
            'tax_total' => [
                'numeric',
                'nullable',
            ],
            'tax_rate' => [
                'numeric',
                'required',
            ],
            'grand_total' => [
                'numeric',
                'required',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'required',
            ],
        ];
    }
}
