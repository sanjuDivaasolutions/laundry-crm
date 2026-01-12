<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreParentCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('parent_category_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
