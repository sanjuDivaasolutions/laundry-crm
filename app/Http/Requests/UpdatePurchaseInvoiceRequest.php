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

use App\Models\PurchaseInvoice;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdatePurchaseInvoiceRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('purchase_invoice_edit');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('supplier');
        $this->setObjectId('purchase_order');
        $this->setObjectId('company');
    }

    public function rules()
    {
        return [
            'company_id'        => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'purchase_order_id' => [
                'integer',
                'exists:purchase_orders,id',
                'required',
            ],
            'invoice_number'    => [
                'string',
                'required',
                'unique:purchase_invoices,invoice_number,' . request()->route('purchase_invoice')->id,
            ],
            'date'              => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'due_date'          => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'supplier_id'       => [
                'integer',
                'exists:suppliers,id',
                'required',
            ],
            'remark'            => [
                'string',
                'nullable',
            ],
            'user_id'           => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
            'type'              => [
                'nullable',
                'in:' . implode(',', Arr::pluck(PurchaseInvoice::TYPE_SELECT, 'value')),
            ],
            'reference_no'      => [
                'string',
                'nullable',
            ],
            'sub_total'         => [
                'numeric',
                'nullable',
            ],
            'tax_total'         => [
                'numeric',
                'nullable',
            ],
            'tax_rate'          => [
                'numeric',
                'nullable',
            ],
            'grand_total'       => [
                'numeric',
                'nullable',
            ],
        ];
    }
}
