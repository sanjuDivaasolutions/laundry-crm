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
 *  *  Last modified: 05/02/25, 7:33 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('payment_create');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('payment_mode');
        $this->generateCode();
        $this->setUser();
    }

    public function rules()
    {
        return [
            'payment_type'        => [
                'required',
                'in:' . implode(',', Arr::pluck(Payment::PAYMENT_TYPE_SELECT, 'value')),
            ],
            'tran_type'           => [
                'required',
                'in:' . implode(',', Arr::pluck(Payment::TRAN_TYPE_SELECT, 'value')),
            ],
            'sales_order_id'      => [
                'integer',
                'exists:sales_orders,id',
                'nullable',
            ],
            'sales_invoice_id'    => [
                'integer',
                'exists:sales_invoices,id',
                'nullable',
            ],
            'purchase_order_id'   => [
                'integer',
                'exists:purchase_orders,id',
                'nullable',
            ],
            'purchase_invoice_id' => [
                'integer',
                'exists:purchase_orders,id',
                'nullable',
            ],
            'payment_mode_id'     => [
                'integer',
                'exists:payment_modes,id',
                'required',
            ],
            'order_no'            => [
                'string',
                'required',
                'unique:payments',
            ],
            'reference_no'        => [
                'string',
                'nullable',
            ],
            'payment_date'        => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'remarks'             => [
                'string',
                'nullable',
            ],
            'amount'              => [
                'numeric',
                'nullable',
            ],
            'user_id'             => [
                'integer',
                'exists:users,id',
                'required',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('payment_type') !== 'si') {
                return;
            }

            $salesInvoiceId = (int) $this->input('sales_invoice_id');
            if (!$salesInvoiceId) {
                return;
            }

            $invoice = SalesInvoice::find($salesInvoiceId);
            if (!$invoice) {
                return;
            }

            $currentStatus = $invoice->syncPaymentStatus();
            if ($currentStatus === 'paid') {
                $message = __('Payment cannot be recorded because this sales invoice is already marked as paid.');
                $validator->errors()->add('sales_invoice_id', $message);
            }
        });
    }

    private function generateCode()
    {
        $field = 'order_no';
        $config = [
            'table'  => 'payments',
            'field'  => $field,
            'prefix' => 'PAY-'
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);
    }
}
