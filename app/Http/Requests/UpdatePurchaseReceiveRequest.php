<?php

namespace App\Http\Requests;

use App\Models\PurchaseReceive;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdatePurchaseReceiveRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('purchase_receive_edit');
    }

    public function rules()
    {
        return [
            'code' => [
                'string',
                'required',
            ],
            'date' => [
                'date_format:' . config('project.date_format'),
                'required',
            ],
            'remarks' => [
                'string',
                'nullable',
            ],
            'user_id' => [
                'integer',
                'exists:users,id',
                'required',
            ],
        ];
    }
}
