<?php

namespace App\Http\Requests;

use App\Models\Payment;
use App\Traits\CustomFormRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdatePaymentRequest extends FormRequest
{
    use CustomFormRequest;

    private string $requestType;

    private array $idObjects = ['payment_mode'];

    private array $valueObjects = [];

    private array $stringArrays = [];

    public function prepareForValidation(): void
    {
        $this->prepareObjects();
    }

    public function authorize()
    {
        return Gate::allows('payment_edit');
    }

    public function rules()
    {
        return [
            'payment_type' => [
                'required',
                'in:'.implode(',', Arr::pluck(Payment::PAYMENT_TYPE_SELECT, 'value')),
            ],
            'tran_type' => [
                'required',
                'in:'.implode(',', Arr::pluck(Payment::TRAN_TYPE_SELECT, 'value')),
            ],
            'sales_order_id' => [
                'integer',
                'exists:sales_orders,id',
                'nullable',
            ],
            'sales_invoice_id' => [
                'integer',
                'exists:sales_invoices,id',
                'nullable',
            ],
            'purchase_order_id' => [
                'integer',
                'exists:purchase_orders,id',
                'nullable',
            ],
            'purchase_invoice_id' => [
                'integer',
                'exists:purchase_orders,id',
                'nullable',
            ],
            'payment_mode_id' => [
                'integer',
                'exists:payment_modes,id',
                'required',
            ],
            'order_no' => [
                'string',
                'required',
                'unique:payments,order_no,'.request()->route('payment')->id,
            ],
            'reference_no' => [
                'string',
                'nullable',
            ],
            'payment_date' => [
                'date_format:'.config('project.date_format'),
                'required',
            ],
            'remarks' => [
                'string',
                'nullable',
            ],
            'amount' => [
                'numeric',
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
