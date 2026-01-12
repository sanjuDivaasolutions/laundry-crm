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
 *  *  Last modified: 21/01/25, 4:49 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Models\Company;
use App\Services\QuotationService;
use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = [
        'company',
        'buyer',
    ];

    private array $valueObjects = [];

    private array $stringArrays = [
        'items',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->requestType = request()->getMethod();
    }

    public function prepareForValidation(): void
    {
        $this->setObjectIds($this->idObjects);
        $this->setObjectValues($this->valueObjects);
        $this->convertBulkToArray($this->stringArrays);

        $this->setIfNull('sub_total', 0);
        $this->setIfNull('tax_total', 0);
        $this->setIfNull('grand_total', 0);

        $this->setIfNull('state_id', 1);
        $this->setUser();

        if ($this->isCreateRequest()) {
            $company = Company::query()->find($this->company_id);
            $this->set('order_no', QuotationService::getCode($company, 'quotations'));
        }
        // dd($this->all());
    }

    private function isUpdateRequest(): bool
    {
        return in_array($this->requestType, ['PATCH', 'PUT']);
    }

    private function isCreateRequest(): bool
    {
        return $this->requestType === 'POST';
    }

    public function rules(): array
    {
        $rules = [
            'order_no' => ['required', 'unique:quotations,order_no'],
            'reference_no' => ['nullable'],
            'company_id' => ['required', 'exists:companies,id'],
            'buyer_id' => ['required', 'exists:buyers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'date' => ['required'],
            'sub_total' => ['required', 'numeric'],
            'tax_total' => ['required', 'numeric'],
            'grand_total' => ['required', 'numeric'],
            'remark' => ['nullable'],
            'expected_delivery_date' => ['nullable'],
            'state_id' => ['required', 'exists:states,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array'],
        ];

        if ($this->isUpdateRequest()) {
            $rules['order_no'] = ['required', 'unique:quotations,order_no,'.request()->route('quotation')->id];
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }
}
