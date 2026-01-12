<?php

namespace App\Http\Requests\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class ConvertQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_notes' => 'nullable|string|max:1000',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'expected_delivery_date' => 'nullable|date|after:today',
            'payment_terms' => 'nullable|string|max:255',
            'sales_person_id' => 'nullable|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'convert_all_items' => 'boolean',
            'selected_items' => 'required_if:convert_all_items,false|array',
            'selected_items.*.id' => 'required|exists:quotation_items,id',
            'selected_items.*.quantity' => 'required|numeric|min:1',
            'selected_items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Please select a warehouse for the sales order.',
            'selected_items.required_if' => 'Please select at least one item to convert.',
            'expected_delivery_date.after' => 'Expected delivery date must be after today.',
        ];
    }
}
