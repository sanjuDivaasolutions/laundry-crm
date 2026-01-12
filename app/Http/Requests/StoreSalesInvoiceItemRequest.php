<?php

namespace App\Http\Requests;

use App\Models\SalesInvoiceItem;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreSalesInvoiceItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sales_invoice_item_create');
    }

    public function rules()
    {
        return [
            'sales_invoice_id' => [
                'integer',
                'exists:sales_invoices,id',
                'required',
            ],
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'sku' => [
                'string',
                'nullable',
            ],
            'unit_id' => [
                'integer',
                'exists:units,id',
                'nullable',
            ],
            'remark' => [
                'string',
                'nullable',
            ],
            'quantity' => [
                'numeric',
                'nullable',
            ],
            'rate' => [
                'numeric',
                'required',
            ],
            'original_rate' => [
                'numeric',
                'nullable',
            ],
            'amount' => [
                'numeric',
                'nullable',
            ],
        ];
    }
}
