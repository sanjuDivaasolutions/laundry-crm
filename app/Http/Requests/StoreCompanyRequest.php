<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 22/01/25, 4:52 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\CompanyService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    use CustomFormRequest;

    private $arrayFields = [
        'image',
    ];

    public function authorize()
    {
        return Gate::allows('company_edit');
    }

    protected function prepareForValidation()
    {
        $this->set('code', CompanyService::getCompanyCode($this->name));
        $this->setObjectId('city');
        $this->setObjectId('state');
        $this->setObjectId('country');
        $this->setObjectId('warehouse');
        $this->convertBulkToArray($this->arrayFields);
    }

    public function rules()
    {
        return [
            'code'              => [
                'string',
                'required',
                'unique:companies,code',
            ],
            'name'              => [
                'string',
                'required',
            ],
            'address_1'         => [
                'string',
                'nullable',
            ],
            'address_2'         => [
                'string',
                'nullable',
            ],
            'country_id'        => [
                'integer',
                'exists:countries,id',
                'nullable',
            ],
            'state_id'          => [
                'integer',
                'exists:states,id',
                'nullable',
            ],
            'city_id'           => [
                'integer',
                'exists:cities,id',
                'nullable',
            ],
            'postal_code'       => [
                'string',
                'nullable',
            ],
            'registration_code' => [
                'string',
                'nullable',
            ],
            'department'        => [
                'array',
            ],
            'department.*.id'   => [
                'integer',
                'exists:departments,id',
            ],
            'warehouse_id'      => [
                'integer',
                'exists:warehouses,id',
                'nullable',
            ],
        ];
    }

}
