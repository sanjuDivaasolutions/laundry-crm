<?php

namespace App\Http\Requests;

use App\Models\ProductStock;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreProductStockRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_stock_create');
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
                'date_format:' . config('project.date_format'),
                'required',
            ],
        ];
    }
}
