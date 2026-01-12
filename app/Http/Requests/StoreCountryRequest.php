<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('country_create');
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'active' => 1,
        ]);
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'unique:countries',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
