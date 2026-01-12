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
 *  *  Last modified: 15/01/25, 2:15 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('supplier_create');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('payment_term');
        $this->setObjectId('currency');
        $this->generateCode();
        $this->merge([
            'shipping_same_as_billing' => filter_var($this->input('shipping_same_as_billing'), FILTER_VALIDATE_BOOLEAN),
            'is_agent' => filter_var($this->input('is_agent', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules()
    {
        return [
            'code'                => [
                'string',
                'required',
            ],
            'display_name'        => [
                'string',
                'required',
            ],
            'name'                => [
                'string',
                'required',
            ],
            'payment_term_id'     => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'billing_address_id'  => [
                'integer',
                'exists:contact_addresses,id',
                'nullable',
            ],
            'shipping_address_id' => [
                'integer',
                'exists:contact_addresses,id',
                'nullable',
            ],
            'shipping_same_as_billing' => [
                'boolean',
            ],
            'active'              => [
                'boolean',
            ],
            'is_agent'            => [
                'boolean',
            ],
            'currency_id'         => [
                'integer',
                'exists:currencies,id',
                'nullable',
            ],
            'email'               => [
                'email',
                'nullable',
            ],
            'phone'               => [
                'string',
                'nullable',
            ],
            'remarks'             => [
                'string',
                'nullable',
            ],
        ];
    }

    private function generateCode()
    {
        $field = 'code';
        $config = [
            'table'  => 'suppliers',
            'field'  => $field,
            'prefix' => 'SUP-'
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);
    }
}
