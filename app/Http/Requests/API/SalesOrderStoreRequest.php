<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SalesOrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_number' => 'required|string|max:50|unique:sales_orders,order_number',
            'buyer_id' => 'required|exists:buyers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'order_type' => 'required|in:product,service',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'sub_total' => 'required|numeric|min:0',
            'tax_total' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'status' => 'nullable|in:draft,pending,confirmed,converted,cancelled',
            'notes' => 'nullable|string|max:1000',
            'commission' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.tax_total' => 'nullable|numeric|min:0',
            'items.*.sub_total' => 'required|numeric|min:0',
            'items.*.grand_total' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'order_number.required' => 'Order number is required.',
            'order_number.unique' => 'Order number must be unique.',
            'buyer_id.required' => 'Buyer is required.',
            'buyer_id.exists' => 'Selected buyer is invalid.',
            'agent_id.exists' => 'Selected agent is invalid.',
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Selected company is invalid.',
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user is invalid.',
            'order_type.required' => 'Order type is required.',
            'order_type.in' => 'Order type must be either product or service.',
            'date.required' => 'Order date is required.',
            'date.date' => 'Please provide a valid order date.',
            'due_date.after_or_equal' => 'Due date must be after or equal to order date.',
            'sub_total.required' => 'Sub total is required.',
            'sub_total.min' => 'Sub total cannot be negative.',
            'grand_total.required' => 'Grand total is required.',
            'grand_total.min' => 'Grand total cannot be negative.',
            'status.in' => 'Status must be one of: draft, pending, confirmed, converted, cancelled.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'Selected product is invalid.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
        ];
    }
}