<?php

namespace App\Http\Requests;

use App\Models\PaymentMode;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdatePaymentModeRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('payment_mode_edit');
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
