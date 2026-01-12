<?php

namespace App\Http\Requests;

use App\Models\ShipmentMode;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreShipmentModeRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('shipment_mode_create');
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
