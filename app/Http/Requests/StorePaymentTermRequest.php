<?php

namespace App\Http\Requests;

use App\Models\PaymentTerm;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StorePaymentTermRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('payment_term_create');
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
