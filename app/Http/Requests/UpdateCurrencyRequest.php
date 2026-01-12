<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('currency_edit');
    }

    public function rules()
    {
        return [
            'code' => [
                'string',
                'required',
                'unique:currencies,code,'.request()->route('currency')->id,
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
