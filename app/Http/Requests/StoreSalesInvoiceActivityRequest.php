<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesInvoiceActivityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sales_invoice_activity_create');
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'required',
                'unique:sales_invoice_activities',
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
