<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductStockRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_stock_edit');
    }

    public function rules()
    {
        return [
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'warehouse_id' => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
            'on_hand' => [
                'string',
                'required',
            ],
            'in_transit' => [
                'string',
                'nullable',
            ],
            'modified' => [
                'date_format:'.config('project.date_format'),
                'required',
            ],
        ];
    }
}
