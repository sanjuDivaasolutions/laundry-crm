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
 *  *  Last modified: 05/02/25, 6:04 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class InventoryAdjustmentRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = [
        'product',
        'shelf',
        'target_shelf',
    ];

    private array $valueObjects = [
        'reason'
    ];

    private array $stringArrays = [];

    public function __construct()
    {
        parent::__construct();
        $this->requestType = request()->getMethod();
    }

    public function prepareForValidation(): void
    {
        $this->prepareObjects();
        $this->setUser();
        if ($this->isCreateRequest()) {
            $this->generateCode();
        }
    }

    public function rules(): array
    {
        return [
            'code'              => ['required'],
            'date'              => ['required'],
            'reason'            => ['required'],
            'remark'            => ['nullable'],
            'product_id'        => ['required', 'exists:products,id'],
            'shelf_id'          => ['required', 'exists:shelves,id'],
            'target_shelf_id'   => ['required_if:reason,move', 'nullable', 'exists:shelves,id'],
            'adjusted_quantity' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->input('reason') == 'move' && $value < 0) {
                        $fail('The adjusted quantity cannot be negative in case of stock movement.');
                    }
                },
            ],
            'user_id'           => ['required', 'exists:users,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    private function generateCode(): void
    {
        $field = 'code';
        $config = [
            'table'  => 'inventory_adjustments',
            'field'  => $field,
            'prefix' => 'ADJ-'
        ];
        $code = UtilityService::generateCode($config);
        $this->set($field, $code);
    }
}
