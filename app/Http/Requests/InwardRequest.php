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
 *  *  Last modified: 05/02/25, 7:58 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class InwardRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = [
        'company',
        'supplier',
        'warehouse',
        'state',
    ];

    private array $valueObjects = [];

    private array $stringArrays = [
        'items',
        'taxes',
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

        $this->setItems();

        $this->setIfNull('currency_rate', 1);
        $this->setIfNull('tax_rate', 5);
        $this->setIfNull('grand_total', 0);
        $this->setIfNull('sub_total', 0);
        $this->setIfNull('tax_total', 0);

        if ($this->isCreateRequest()) {
            $this->generateCode();
            $this->setUser();
        }
    }

    public function rules(): array
    {
        $rules = [
            'invoice_number' => ['required', 'unique:inwards'],
            'reference_no' => ['nullable'],
            'date' => ['nullable', 'string'],
            'remark' => ['nullable'],
            'currency_rate' => ['required', 'numeric'],
            'sub_total' => ['required', 'numeric'],
            'tax_total' => ['required', 'numeric'],
            'tax_rate' => ['required', 'numeric'],
            'grand_total' => ['required', 'numeric'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.rate' => ['required', 'numeric'],
            'items.*.amount' => ['required', 'numeric'],
            'items.*.sku' => ['nullable'],
            'items.*.description' => ['nullable'],
        ];

        if ($this->isUpdateRequest()) {
            $rules['invoice_number'] = ['required', 'unique:inwards,invoice_number,'.$this->route('inward')->id];
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return Gate::allows('inward_create');
    }

    protected function generateCode(): void
    {
        $field = 'invoice_number';
        $config = [
            'table' => 'inwards',
            'field' => $field,
            'prefix' => 'INW-',
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);
    }

    private function isUpdateRequest(): bool
    {
        return in_array($this->requestType, ['PATCH', 'PUT']);
    }

    private function isCreateRequest(): bool
    {
        return $this->requestType === 'POST';
    }

    private function setItems(): void
    {
        $items = $this->input('items');
        foreach ($items as &$i) {
            if (isset($i['product']) && $i['product']) {
                $i['product_id'] = $i['product']['id'];
                unset($i['product']);
            }
            if (isset($i['unit']) && $i['unit']) {
                $i['unit_id'] = $i['unit']['id'];
                unset($i['unit']);
            }
            $i['amount'] = $i['quantity'] * $i['rate'];

            $shelves = [];
            $shelves[] = [
                'id' => isset($i['shelf']['parent_id']) ? $i['shelf']['parent_id'] : null,
                'shelf_id' => $i['shelf']['id'],
                'quantity' => $i['quantity'],
            ];
            $i['shelf'] = $shelves;
        }
        $this->set('items', $items);
    }
}
