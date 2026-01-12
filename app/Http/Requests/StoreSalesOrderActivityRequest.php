<?php

namespace App\Http\Requests;

use App\Models\SalesOrderActivity;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreSalesOrderActivityRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sales_order_activity_create');
    }

    public function rules()
    {
        return [
            'sale_order_id' => [
                'integer',
                'exists:sales_orders,id',
                'required',
            ],
            'title' => [
                'string',
                'required',
            ],
            'is_active' => [
                'boolean',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'nullable',
            ],
        ];
    }
}
