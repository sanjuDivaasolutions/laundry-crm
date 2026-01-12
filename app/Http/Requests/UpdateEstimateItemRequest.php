<?php

namespace App\Http\Requests;

use App\Models\EstimateItem;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateEstimateItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('estimate_item_edit');
    }

    public function rules()
    {
        return [
            'estimate_id' => [
                'integer',
                'exists:estimates,id',
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
                'nullable',
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
                'required',
            ],
            'quantity' => [
                'numeric',
                'nullable',
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
