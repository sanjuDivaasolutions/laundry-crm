<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentTermRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('payment_term_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'days' => [
                'integer',
                'min:-2147483648',
                'max:2147483647',
                'required',
            ],
        ];
    }
}
