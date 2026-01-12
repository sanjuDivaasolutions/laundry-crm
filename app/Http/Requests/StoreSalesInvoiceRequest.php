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
 *  *  Last modified: 12/02/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Models\SalesInvoice;
use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreSalesInvoiceRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('sales_invoice_create');
    }

    public function prepareForValidation(): void
    {
        $this->setUser();
        $this->setObjectId('buyer');
        $this->setObjectId('agent');
        $this->setObjectId('payment_term');
        $this->setObjectId('warehouse');
        $this->setObjectId('sales_order');
        $this->setObjectId('company');
        $this->setObjectId('state');

        $this->convertToArray('taxes');

        $this->set('order_type', 'product');

        $this->set('tax_rate', 5);

        $this->generateCode();
    }

    public function rules(): array
    {
        return [
            'company_id'       => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'warehouse_id'     => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
            'state_id'         => [
                'integer',
                'exists:states,id',
                'required',
            ],
            'invoice_number'   => [
                'string',
                'required',
                'unique:sales_invoices,invoice_number',
            ],
            'sales_order_id'   => [
                'integer',
                'exists:sales_orders,id',
                'nullable',
            ],
            'payment_term_id'  => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'date'             => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'due_date'         => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'buyer_id'         => [
                'integer',
                'exists:buyers,id',
                'nullable',
            ],
            'agent_id'         => [
                'integer',
                'exists:suppliers,id',
                'nullable',
            ],
            'remark'           => [
                'string',
                'nullable',
            ],
            'user_id'          => [
                'integer',
                'exists:users,id',
                'required',
            ],
            'type'             => [
                'nullable',
                'in:' . implode(',', Arr::pluck(SalesInvoice::TYPE_SELECT, 'value')),
            ],
            'order_type'       => [
                'required',
                'in:service,product',
            ],
            'reference_no'     => [
                'string',
                'nullable',
            ],
            'sub_total'        => [
                'numeric',
                'required',
            ],
            'tax_total'        => [
                'numeric',
                'required',
            ],
            'tax_rate'         => [
                'numeric',
                'required',
            ],
            'grand_total'      => [
                'numeric',
                'required',
            ],
            'commission'       => [
                'numeric',
                'min:0',
                'max:100',
                'nullable',
            ],
            'commission_total' => [
                'numeric',
                'nullable',
            ],
            'is_taxable'       => [
                'boolean',
                'required',
            ],
        ];
    }

    protected function generateCode(): void
    {
        $field = 'invoice_number';
        $config = [
            'table'  => 'sales_invoices',
            'field'  => $field,
            'prefix' => 'SI-'
        ];
        $code = UtilityService::generateCode($config);
        $this->set($field, $code);

    }
}
