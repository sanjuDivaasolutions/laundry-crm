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
 *  *  Last modified: 05/02/25, 7:30 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreContractRequest extends FormRequest
{
    use CustomFormRequest;

    private $arrayFields = [
        'items',
        'term',
    ];

    public function authorize()
    {
        return Gate::allows('contract_create');
    }

    public function prepareForValidation()
    {
        $this->setObjectId('buyer');
        $this->setObjectId('company');
        $this->convertBulkToArray($this->arrayFields);
        $this->prepareItemArray();
        $this->generateCode();
        $this->setEndDate();
        $this->setUser();

        $contractType = $this->input('contract_type');
        $contractTypeValue = isset($contractType['value']) ? $contractType['value'] : 'default';
        $revision = $this->input('revision', []);
        $revision['contract_type'] = $contractTypeValue;
        $this->set('revision', $revision);
    }

    public function rules()
    {
        return [
            'code'                         => [
                'string',
                'required',
                'unique:contracts',
            ],
            'company_id'                   => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'buyer_id'                     => [
                'integer',
                'exists:buyers,id',
                'required',
            ],
            'date'                         => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'other_terms'                  => [
                'string',
                'nullable',
            ],
            'remark'                       => [
                'string',
                'nullable',
            ],
            'term'                         => [
                'array',
            ],
            'term.*.id'                    => [
                'integer',
                'exists:contract_terms,id',
            ],
            'revision.contract_type'       => [
                'string',
                'required',
            ],
            'revision.start_date'          => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'revision.end_date'            => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'revision.limited_installment' => [
                'boolean',
                'required',
            ],
            'revision.installment_count'   => [
                'integer',
                'min:0',
                'max:2147483647',
                'required',
            ],
            'revision.sub_total'           => [
                'numeric',
                'required',
            ],
            'revision.tax_total'           => [
                'numeric',
                'required',
            ],
            'revision.tax_rate'            => [
                'numeric',
                'required',
            ],
            'revision.grand_total'         => [
                'numeric',
                'required',
            ],
            'revision.user_id'             => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
            'user_id'                      => [
                'integer',
                'exists:users,id',
                'required',
            ],
        ];
    }

    public function prepareItemArray()
    {
        //$items = ContractService::getItemArray($this);
        //$this->set('items', $items);
    }

    public function setEndDate()
    {
        $revision = $this->input('revision', []);
        if (!$this->get('revision.limited_installment', false)) {
            $revision['end_date'] = null;
            $revision['installment_count'] = 0;
            $this->set('revision', $revision);
            return;
        }
        $startDate = Carbon::createFromFormat(config('project.date_format'), $this->get('start_date'));
        $installmentCount = $this->get('revision.installment_count');
        $endDate = $startDate->addMonths($installmentCount)->format(config('project.date_format'));
        $revision['end_date'] = $endDate;
        $this->set('revision', $revision);
    }

    private function generateCode()
    {
        $field = 'code';
        $config = [
            'table'  => 'contracts',
            'field'  => $field,
            'prefix' => 'CON-'
        ];
        $code = UtilityService::generateCode($config);
        $this->set($field, $code);
    }
}
