<?php

namespace App\Http\Requests;

use App\Models\ProductInventory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UpdateProductInventoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_inventory_edit');
    }

    public function rules()
    {
        return [
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'order_no' => [
                'string',
                'nullable',
            ],
            'warehouse_id' => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
            'batch_id' => [
                'integer',
                'exists:product_batches,id',
                'nullable',
            ],
            'shelf_id' => [
                'integer',
                'exists:shelves,id',
                'nullable',
            ],
            'reason' => [
                'required',
                'in:' . implode(',', Arr::pluck(ProductInventory::REASON_SELECT, 'value')),
            ],
            'date' => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'rate' => [
                'numeric',
                'required',
            ],
            'quantity' => [
                'integer',
                'min:-2147483648',
                'max:2147483647',
                'nullable',
            ],
            'amount' => [
                'numeric',
                'required',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'required',
            ],
            'unit_id' => [
                'integer',
                'exists:units,id',
                'nullable',
            ],
        ];
    }
}
