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
 *  *  Last modified: 23/01/25, 5:22 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use App\Services\UtilityService;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = [
        'sales_invoice'
    ];

    private array $valueObjects = [];

    private array $stringArrays = [
        'items'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->requestType = request()->getMethod();
    }

    public function prepareForValidation(): void
    {
        $this->prepareObjects();

        $this->generateCode();
        $this->setUser();

        $this->prepareItems();
    }

    public function authorize(): bool
    {
        return Gate::allows('package_create');
    }

    public function rules(): array
    {
        return [
            'sales_order_id'   => [
                'integer',
                'exists:sales_orders,id',
                'nullable',
            ],
            'sales_invoice_id' => [
                'integer',
                'exists:sales_invoices,id',
                'nullable',
            ],
            'code'             => [
                'string',
                'required',
            ],
            'reference_no'     => [
                'string',
                'nullable',
            ],
            'date'             => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'remarks'          => [
                'string',
                'nullable',
            ],
            'user_id'          => [
                'integer',
                'exists:users,id',
                'required',
            ],
            'packing_type_id'  => [
                'integer',
                'exists:packing_types,id',
                'nullable',
            ],
        ];
    }

    protected function generateCode(): void
    {
        $field = 'code';
        $config = [
            'table'  => 'packages',
            'field'  => $field,
            'prefix' => 'PKG-'
        ];
        $code = UtilityService::generateCode($config);
        $this->set($field, $code);
    }

    private function prepareItems(): void
    {
        $unsets = ['product', 'unit'];
        $items = $this->items;
        foreach ($items as &$item) {
            $item['id'] = $this->isCreateRequest() ? null : $item['id'];
            $item['package_id'] = $this->isCreateRequest() ? null : $item['package_id'];
            foreach ($unsets as $unset) {
                unset($item[$unset]);
            }
        }
        $this->set('items', $items);
    }

}
