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
 *  *  Last modified: 05/02/25, 7:12 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class PaymentModeRequest extends FormRequest
{
    use CustomFormRequest;

    private string $moduleId = 'payment_mode';
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

        $this->setActive();
    }

    private function isUpdateRequest(): bool
    {
        return in_array($this->requestType, ['PATCH', 'PUT']);
    }

    private function isCreateRequest(): bool
    {
        return $this->requestType === 'POST';
    }

    public function authorize()
    {
        $permission = $this->isUpdateRequest() ? '_edit' : '_create';
        return Gate::allows($this->moduleId . $permission);
    }

    public function rules(): array
    {
        return [
            'name'   => [
                'string',
                'required',
            ],
            'active' => [
                'boolean',
            ],
        ];
    }
}
