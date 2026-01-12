<?php

namespace App\Http\Requests;

use App\Models\Country;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

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
