<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesInvoiceActivityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sales_invoice_activity_edit');
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'required',
                'unique:sales_invoice_activities,title,'.request()->route('sales_invoice_activity')->id,
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'sale_invoice_id' => [
                'integer',
                'exists:sales_invoices,id',
                'required',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }
}
