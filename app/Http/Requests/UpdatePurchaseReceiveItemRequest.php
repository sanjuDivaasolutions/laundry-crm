<?php

namespace App\Http\Requests;

use App\Models\PurchaseReceiveItem;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdatePurchaseReceiveItemRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('purchase_receive_item_edit');
    }

    public function rules()
    {
        return [
            'purchase_receive_id' => [
                'integer',
                'exists:purchase_receives,id',
                'required',
            ],
            'purchase_order_id' => [
                'integer',
                'exists:purchase_orders,id',
                'nullable',
            ],
            'purchase_invoice_id' => [
                'integer',
                'exists:purchase_invoices,id',
                'nullable',
            ],
            'product_id' => [
                'integer',
                'exists:products,id',
                'required',
            ],
            'quantity' => [
                'numeric',
                'required',
            ],
        ];
    }
}
