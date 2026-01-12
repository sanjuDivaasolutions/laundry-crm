<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class AgentStoreRequest extends FormRequest
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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:agents,code',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'commission_type' => 'required|in:percentage,fixed',
            'fixed_commission' => 'required|numeric|min:0',
            'active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Agent code is required.',
            'code.unique' => 'Agent code must be unique.',
            'name.required' => 'Agent name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'Email address must be unique.',
            'commission_rate.required' => 'Commission rate is required.',
            'commission_rate.max' => 'Commission rate cannot exceed 100%.',
            'commission_type.required' => 'Commission type is required.',
            'commission_type.in' => 'Commission type must be either percentage or fixed.',
            'fixed_commission.required' => 'Fixed commission amount is required.',
            'fixed_commission.min' => 'Fixed commission cannot be negative.',
        ];
    }
}
