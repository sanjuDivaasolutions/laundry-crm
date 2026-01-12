<?php

namespace App\Http\Requests;

use App\Models\PackageItem;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StorePackageItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('package_item_create');
    }

    public function rules()
    {
        return [
            'package_id' => [
                'integer',
                'exists:packages,id',
                'required',
            ],
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
            'quantity' => [
                'numeric',
                'nullable',
            ],
        ];
    }
}
