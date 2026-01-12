<?php

namespace App\Http\Requests;

use App\Models\ProductBatch;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateProductBatchRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('product_batch_edit');
    }

    public function rules()
    {
        return [
            'shelf_id' => [
                'integer',
                'exists:shelves,id',
                'required',
            ],
            'name' => [
                'string',
                'required',
            ],
            'manufacturer_batch_no' => [
                'string',
                'nullable',
            ],
            'manufacturer_date' => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
            'active' => [
                'boolean',
            ],
            'expiry_date' => [
                'date_format:' . config('project.date_format'),
                'nullable',
            ],
        ];
    }
}
