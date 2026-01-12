<?php

namespace App\Http\Requests;

use App\Models\ProductPrice;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateProductPriceRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_price_edit');
    }

    public function rules()
    {
        return [
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'unit_id' => [
                'integer',
                'exists:units,id',
                'required',
            ],
            'purchase_price' => [
                'numeric',
                'required',
            ],
            'sale_price' => [
                'numeric',
                'required',
            ],
            'lowest_sale_price' => [
                'numeric',
                'required',
            ],
        ];
    }
}
