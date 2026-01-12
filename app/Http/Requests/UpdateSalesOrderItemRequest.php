<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sales_order_item_edit');
    }

    public function rules()
    {
        return [
            'sales_order_id' => [
                'integer',
                'exists:sales_orders,id',
                'required',
            ],
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'sku' => [
                'string',
                'nullable',
            ],
            'unit_id' => [
                'integer',
                'exists:units,id',
                'required',
            ],
            'description' => [
                'string',
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
            'quantity' => [
                'numeric',
                'required',
            ],
            'amount' => [
                'numeric',
                'required',
            ],
            'remarks' => [
                'string',
                'nullable',
            ],
        ];
    }
}
