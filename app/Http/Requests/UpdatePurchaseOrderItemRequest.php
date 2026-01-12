<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('purchase_order_item_edit');
    }

    public function rules()
    {
        return [
            'purchase_order_id' => [
                'integer',
                'exists:purchase_orders,id',
                'nullable',
            ],
            'product_id' => [
                'integer',
                'exists:products,id',
                'nullable',
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
                'required',
            ],
            'rate' => [
                'numeric',
                'required',
            ],
            'quantity' => [
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
