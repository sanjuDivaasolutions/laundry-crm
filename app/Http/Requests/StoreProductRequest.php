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
 *  *  Last modified: 09/01/25, 10:27 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Models\Product;
use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    use CustomFormRequest;

    private array $idObjects = [
        'category',
        'company',
        'unit_01',
        'unit_02',
        'supplier',
    ];

    private array $valueObjects = [
        'type',
    ];

    private array $stringArrays = [
        'features', 'opening', 'prices',
    ];

    public function authorize(): bool
    {
        return Gate::allows('product_create');
    }

    public function prepareForValidation(): void
    {
        $this->setObjectIds($this->idObjects);
        $this->setObjectValues($this->valueObjects);
        $this->convertBulkToArray($this->stringArrays);

        $this->generateCode();
        $this->setUser();
        $this->setActive();

        $this->set('has_inventory', 1);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'string',
                'required',
                'unique:products',
            ],
            'name' => [
                'string',
                'required',
            ],
            'sku' => [
                'string',
                'required',
                'unique:products',
            ],
            'type' => [
                'string',
                'required',
                'in:'.implode(',', collect(Product::TYPE_SELECT)->pluck('value')->toArray()),
            ],
            'has_inventory' => [
                'integer',
            ],
            'category_id' => [
                'integer',
                'exists:categories,id',
                'nullable',
            ],
            'company_id' => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'supplier_id' => [
                'integer',
                'exists:suppliers,id',
                'nullable',
            ],
            'active' => [
                'boolean',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
            'manufacturer' => [
                'string',
                'nullable',
            ],
            'unit_01_id' => [
                'integer',
                'exists:units,id',
                'nullable',
            ],
            'unit_02_id' => [
                'integer',
                'exists:units,id',
                'nullable',
            ],
            'is_returnable' => [
                'boolean',
            ],
        ];
    }

    private function generateCode(): void
    {
        $prefix = 'PRO-';
        $field = 'code';
        $config = [
            'table' => 'products',
            'field' => $field,
            'prefix' => $prefix,
        ];
        $code = UtilityService::generateCode($config);
        $this->merge([$field => $code]);
    }
}
