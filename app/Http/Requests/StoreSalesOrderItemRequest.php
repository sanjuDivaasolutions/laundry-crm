<?php

namespace App\Http\Requests;

use App\Models\SalesOrderItem;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreSalesOrderItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sales_order_item_create');
    }

    public function rules()
    {
        return [
            'sale_order_id' => [
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
