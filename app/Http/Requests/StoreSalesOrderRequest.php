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
use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreSalesOrderRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('sales_order_create');
    }

    public function prepareForValidation()
    {
        $this->setUser();
        $this->setObjectId('buyer');
        $this->setObjectId('payment_term');
        $this->setObjectId('warehouse');
        $this->setObjectId('company');
        $this->generateCode();
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
                /* 'required', */
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

    protected function generateCode()
    {
        $field = 'so_number';
        $config = [
            'table' => 'sales_orders',
            'field' => $field,
            'prefix' => 'SO-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);

    }

    public function messages()
    {
        return [
            'so_number.required' => 'SO Number is required',
            'date.required' => 'Date is required',
            'buyer_id.required' => 'Importer is required',
            'payment_term_id.required' => 'Payment Term is required',
            'sub_total.required' => 'Sub Total is required',
            'grand_total.required' => 'Grand Total is required',
        ];
    }
}
