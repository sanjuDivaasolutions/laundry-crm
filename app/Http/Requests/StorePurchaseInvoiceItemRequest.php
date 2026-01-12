<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseInvoiceItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('purchase_invoice_item_create');
    }

    public function rules()
    {
        return [
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'sku' => [
                'string',
                'nullable',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'unit_id' => [
                'integer',
                'exists:units,id',
                'nullable',
            ],
            'rate' => [
                'numeric',
                'nullable',
            ],
            'quantity' => [
                'numeric',
                'nullable',
            ],
            'amount' => [
                'numeric',
                'nullable',
            ],
            'purchase_invoice_id' => [
                'integer',
                'exists:purchase_invoices,id',
                'nullable',
            ],
        ];
    }
}
