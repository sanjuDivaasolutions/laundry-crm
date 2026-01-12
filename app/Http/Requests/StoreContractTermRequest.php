<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractTermRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('contract_term_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'sequence' => [
                'integer',
                'min:-2147483648',
                'max:2147483647',
                'nullable',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
