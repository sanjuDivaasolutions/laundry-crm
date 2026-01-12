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

use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = [];

    private array $valueObjects = [];

    private array $stringArrays = [];

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

        if ($this->isCreateRequest()) {
            $this->set('status', 0);
        }
        $this->set('company_id', getUserSetting('company_id'));
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
        //'subject', 'message', 'schedule_at', 'status'
        $rules = [
            'company_id'  => 'required|integer|exists:companies,id',
            'subject'     => 'required|string|max:255',
            'message'     => 'required|string',
            'schedule_at' => 'required',
            'status'      => 'required|integer|in:0,1',

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
}
