<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('country_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:countries,name,'.request()->route('country')->id,
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
