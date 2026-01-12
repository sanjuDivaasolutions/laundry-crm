<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactAddressRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('contact_address_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'nullable',
            ],
            'address_1' => [
                'string',
                'nullable',
            ],
            'address_2' => [
                'string',
                'nullable',
            ],
            'country_id' => [
                'integer',
                'exists:countries,id',
                'nullable',
            ],
            'state_id' => [
                'integer',
                'exists:states,id',
                'nullable',
            ],
            'city_id' => [
                'integer',
                'exists:cities,id',
                'nullable',
            ],
            'postal_code' => [
                'string',
                'nullable',
            ],
            'phone' => [
                'string',
                'required',
            ],
        ];
    }
}
