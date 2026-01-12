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
 *  *  Last modified: 29/01/25, 10:52 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = [
        'company',
        'expense_type',
        'payment_mode',
        'state',
    ];

    private array $valueObjects = [];

    private array $stringArrays = [
        'attachment',
        'taxes'
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

        $this->set('tax_total', 0);
        $this->set('tax_rate', 0);
        $this->set('grand_total', $this->get('sub_total') + $this->get('tax_total'));

        $this->setUser();

        if ($this->isCreateRequest()) {
            $this->generateCode();
        }
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
            'code'            => ['required'],
            'description'     => ['required'],
            'invoice_number'  => ['nullable'],
            'sub_total'       => ['required', 'numeric'],
            'tax_rate'        => ['required', 'numeric'],
            'tax_total'       => ['required', 'numeric'],
            'grand_total'     => ['required', 'numeric'],
            'is_taxable'      => ['required', 'boolean'],
            'date'            => ['required'],
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'payment_mode_id' => ['required', 'exists:payment_modes,id'],
            'state_id'        => ['required', 'exists:states,id'],
            'company_id'      => ['required', 'exists:companies,id'],
            'user_id'         => ['nullable', 'exists:users,id'],
        ];

        if ($this->isCreateRequest()) {
            //
        }

        if ($this->isUpdateRequest()) {
            //
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }

    private function generateCode(): void
    {
        $field = 'code';
        $config = [
            'table'  => 'expenses',
            'field'  => $field,
            'prefix' => 'EXP-'
        ];
        $code = UtilityService::generateCode($config);
        $this->set($field, $code);
    }
}
