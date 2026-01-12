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
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    use CustomFormRequest;

    private array $idObjects = [
        'category',
        'company',
    ];

    private array $valueObjects = [];

    private array $stringArrays = [];

    public function authorize()
    {
        return Gate::allows('product_edit');
    }

    public function prepareForValidation()
    {
        $this->setObjectIds($this->idObjects);
        $this->setObjectValues($this->valueObjects);
        $this->convertBulkToArray($this->stringArrays);

        $this->set('type', 'service');
        $this->set('has_inventory', 0);
        $this->set('unit_01_id', 1);
        $this->set('unit_02_id', 1);
        $this->setIfNull('sku', 'SER-' . time());
    }

    public function rules()
    {
        return [
            'code'          => [
                'string',
                'required',
                'unique:products,code,' . request()->route('product')->id,
            ],
            'name'          => [
                'string',
                'required',
            ],
            'type'          => [
                'string',
                'required',
                'in:' . implode(',', collect(Product::TYPE_SELECT)->pluck('value')->toArray()),
            ],
            'has_inventory' => [
                'integer',
            ],
            'sku'           => [
                'string',
                'required',
                'unique:products,sku,' . request()->route('product')->id,
            ],
            'company_id'    => [
                'integer',
                'exists:companies,id',
                'required',
            ],
            'category_id'   => [
                'integer',
                'exists:categories,id',
                'nullable',
            ],
            'description'   => [
                'string',
                'nullable',
            ],
            'supplier_id'   => [
                'integer',
                'exists:suppliers,id',
                'nullable',
            ],
            'active'        => [
                'boolean',
            ],
            'user_id'       => [
                'integer',
                'exists:users,id',
                'required',
            ],
            'manufacturer'  => [
                'string',
                'nullable',
            ],
            'unit_01_id'    => [
                'integer',
                'exists:units,id',
                'required',
            ],
            'unit_02_id'    => [
                'integer',
                'exists:units,id',
                'required',
            ],
            'is_returnable' => [
                'boolean',
            ],
        ];
    }
}
