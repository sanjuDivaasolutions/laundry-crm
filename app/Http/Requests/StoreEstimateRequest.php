<?php

namespace App\Http\Requests;

use App\Models\Estimate;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreEstimateRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('estimate_create');
    }

    public function rules()
    {
        return [
            'quo_number' => [
                'string',
                'required',
            ],
            'reference_no' => [
                'string',
                'nullable',
            ],
            'warehouse_id' => [
                'integer',
                'exists:warehouses,id',
                'required',
            ],
            'type' => [
                'required',
                'in:'.implode(',', Arr::pluck(Estimate::TYPE_SELECT, 'value')),
            ],
            'buyer_id' => [
                'integer',
                'exists:buyers,id',
                'required',
            ],
            'date' => [
                'date_format:'.config('project.date_format'),
                'nullable',
            ],
            'estimated_shipment_date' => [
                'date_format:'.config('project.date_format'),
                'nullable',
            ],
            'payment_term_id' => [
                'integer',
                'exists:payment_terms,id',
                'nullable',
            ],
            'remarks' => [
                'string',
                'nullable',
            ],
            'sub_total' => [
                'numeric',
                'required',
            ],
            'tax_total' => [
                'numeric',
                'required',
            ],
            'tax_rate' => [
                'numeric',
                'required',
            ],
            'grand_total' => [
                'numeric',
                'required',
            ],
        ];
    }
}
