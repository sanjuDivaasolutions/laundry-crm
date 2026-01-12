<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/12/24, 11:37 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize(): bool
    {
        return Gate::allows('warehouse_create');
    }

    public function prepareForValidation(): void
    {
        $this->setObjectValue('type');
        $this->setObjectId('city');
        $this->setObjectId('country');
        $this->setObjectId('state');


        $this->generateCode();
    }

    public function rules(): array
    {
        return [
            'name'        => [
                'string',
                'required',
            ],
            'code'        => [
                'string',
                'required',
                'unique:warehouses',
            ],
            'address_1'   => [
                'string',
                'nullable',
            ],
            'address_2'   => [
                'string',
                'nullable',
            ],
            'city_id'     => [
                'integer',
                'exists:cities,id',
                'nullable',
            ],
            'state_id'    => [
                'integer',
                'exists:states,id',
                'nullable',
            ],
            'country_id'  => [
                'integer',
                'exists:countries,id',
                'nullable',
            ],
            'postal_code' => [
                'string',
                'nullable',
            ],
            'email'       => [
                'email',
                'nullable',
            ],
            'phone'       => [
                'string',
                'nullable',
            ],
        ];
    }

    private function generateCode(): void
    {
        $field = 'code';
        $config = [
            'table'  => 'warehouses',
            'field'  => $field,
            'prefix' => 'WH-'
        ];
        $code = UtilityService::generateCode($config);
        $this->set($field, $code);
    }
}
