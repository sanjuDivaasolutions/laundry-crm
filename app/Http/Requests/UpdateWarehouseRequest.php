<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('warehouse_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'code' => [
                'string',
                'required',
                'unique:warehouses,code,'.request()->route('warehouse')->id,
            ],
            'address_1' => [
                'string',
                'nullable',
            ],
            'address_2' => [
                'string',
                'nullable',
            ],
            'city_id' => [
                'integer',
                'exists:cities,id',
                'nullable',
            ],
            'state_id' => [
                'integer',
                'exists:states,id',
                'nullable',
            ],
            'country_id' => [
                'integer',
                'exists:countries,id',
                'nullable',
            ],
            'postal_code' => [
                'string',
                'nullable',
            ],
            'email' => [
                'required',
            ],
            'phone' => [
                'string',
                'required',
            ],
        ];
    }
}
