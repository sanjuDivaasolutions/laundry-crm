<?php

namespace App\Http\Requests;

use App\Models\ParentCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

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
