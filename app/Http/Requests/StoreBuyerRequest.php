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
 *  *  Last modified: 15/01/25, 2:08 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreBuyerRequest extends FormRequest
{
    use CustomFormRequest;

    public function authorize()
    {
        return Gate::allows('buyer_create');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('payment_term');
        $this->setObjectId('agent');
        $this->setObjectId('currency');
        $this->generateCode();
        $this->merge([
            'shipping_same_as_billing' => filter_var($this->input('shipping_same_as_billing'), FILTER_VALIDATE_BOOLEAN),
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
            'active'              => [
                'boolean',
            ],
            'currency_id'         => [
                'integer',
                'exists:currencies,id',
                'nullable',
            ],
            'payment_term_id'     => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'agent_id'            => [
                'integer',
                'exists:suppliers,id',
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
            'phone'               => [
                'string',
                'nullable',
            ],
            'email'               => [
                'email',
                'nullable',
            ],
            'agent_name'         => [
                'string',
                'max:255',
                'nullable',
            ],
            'commission_rate'    => [
                'numeric',
                'min:0',
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
            'table'  => 'buyers',
            'field'  => $field,
            'prefix' => 'BUY-'
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);
    }
}
