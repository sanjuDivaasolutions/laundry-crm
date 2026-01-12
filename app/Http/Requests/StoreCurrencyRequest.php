<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('currency_create');
    }

    public function rules()
    {
        return [
            'code' => [
                'string',
                'required',
            ],
            'name' => [
                'string',
                'required',
            ],
            'symbol' => [
                'string',
                'required',
            ],
            'rate' => [
                'numeric',
                'required',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
