<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreShipmentRequest extends FormRequest
{
    use CustomFormRequest;
    public function authorize()
    {
        return Gate::allows('shipment_create');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('package');
        $this->setObjectId('shipment_mode');
        $this->generateCode();

    }

      protected function generateCode()
    {
        $field = 'code';
        $config = [
            'table' =>  'shipments',
            'field' =>  $field,
            'prefix'=>  'SHP-'
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field=>$code]);
       
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
                'unique:shipments',
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
