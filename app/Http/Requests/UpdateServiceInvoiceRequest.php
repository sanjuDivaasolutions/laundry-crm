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
 *  *  Last modified: 10/02/25, 7:22 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Models\SalesInvoice;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateServiceInvoiceRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('sales_invoice_edit');
    }

    public function prepareForValidation()
    {

        $this->setObjectId('buyer');
        $this->setObjectId('payment_term');
        $this->setObjectId('warehouse');
        $this->setObjectId('sales_order');
        $this->setObjectId('company');
        $this->setObjectId('state');

        $this->set('order_type', 'service');

        $this->convertToArray('taxes');

        $this->set('tax_rate', 5);
    }

    public function rules()
    {
        return [
            'company_id'      => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'invoice_number'  => [
                'string',
                'required',
            ],
            'state_id'        => [
                'integer',
                'exists:states,id',
                'required',
            ],
            'sales_order_id'  => [
                'integer',
                'exists:sales_orders,id',
                'nullable',
            ],
            'date'            => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'due_date'        => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'payment_term_id' => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'buyer_id'        => [
                'integer',
                'exists:buyers,id',
                'nullable',
            ],
            'remark'          => [
                'string',
                'nullable',
            ],
            'user_id'         => [
                'integer',
                'exists:users,id',
                'required',
            ],
            'type'            => [
                'nullable',
                'in:' . implode(',', Arr::pluck(SalesInvoice::TYPE_SELECT, 'value')),
            ],
            'order_type'      => [
                'required',
                'in:service,product',
            ],
            'reference_no'    => [
                'string',
                'nullable',
            ],
            'sub_total'       => [
                'numeric',
                'required',
            ],
            'tax_total'       => [
                'numeric',
                'required',
            ],
            'tax_rate'        => [
                'numeric',
                'required',
            ],
            'grand_total'     => [
                'numeric',
                'required',
            ],
            'is_taxable'      => [
                'boolean',
                'required',
            ],
        ];
    }
}
