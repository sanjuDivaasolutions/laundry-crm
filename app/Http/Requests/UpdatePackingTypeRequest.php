<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePackingTypeRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('packing_type_edit');
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
