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
 *  *  Last modified: 16/10/24, 5:22 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Requests;

use App\Models\PurchaseInvoice;
use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StorePurchaseInvoiceRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('purchase_invoice_create');
    }

    public function prepareForValidation()
    {
        $this->generateCode();
        $this->setObjectId('supplier');
        $this->setObjectId('purchase_order');
        $this->set('tax_rate', 20);
        $this->setUser();
        $this->setObjectId('company');
    }

    public function rules()
    {
        return [
            'company_id' => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'purchase_order_id' => [
                'integer',
                'exists:purchase_orders,id',
                'required',
            ],
            'invoice_number' => [
                'string',
                'required',
                'unique:purchase_invoices',
            ],
            'date' => [
                'date_format:'.config('project.date_format'),
                'nullable',
            ],
            'due_date' => [
                'date_format:'.config('project.date_format'),
                'nullable',
            ],
            'supplier_id' => [
                'integer',
                'exists:suppliers,id',
                'nullable',
            ],
            'remark' => [
                'string',
                'nullable',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
            'type' => [
                'nullable',
                'in:'.implode(',', Arr::pluck(PurchaseInvoice::TYPE_SELECT, 'value')),
            ],
            'reference_no' => [
                'string',
                'nullable',
            ],
            'sub_total' => [
                'numeric',
                'nullable',
            ],
            'tax_total' => [
                'numeric',
                'nullable',
            ],
            'tax_rate' => [
                'numeric',
                'nullable',
            ],
            'grand_total' => [
                'numeric',
                'nullable',
            ],
        ];
    }

    protected function generateCode()
    {
        $field = 'invoice_number';
        $config = [
            'table' => 'purchase_invoices',
            'field' => $field,
            'prefix' => 'PI-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);
    }
}
