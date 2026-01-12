<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentModeRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('shipment_mode_edit');
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
