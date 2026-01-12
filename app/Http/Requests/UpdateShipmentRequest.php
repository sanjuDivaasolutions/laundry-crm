<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateShipmentRequest extends FormRequest
{
    use CustomFormRequest;
    public function authorize()
    {
        return Gate::allows('shipment_edit');
    }
    public function prepareForValidation()
    {
        $this->setObjectId('package');
        $this->setObjectId('shipment_mode');
    }

    public function rules()
    {
        return [
            'package_id' => [
                'integer',
                'exists:packages,id',
                'required',
            ],
            'shipment_date' => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'remarks' => [
                'string',
                'nullable',
            ],
            'code' => [
                'string',
                'required',
                'unique:shipments,code,' . request()->route('shipment')->id,
            ],
            'delivery_date' => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'shipment_mode_id' => [
                'integer',
                'exists:shipment_modes,id',
                'required',
            ],
        ];
    }
}
