<?php

/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 18/11/25, 12:00 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertToSalesOrderRequest extends FormRequest
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
            'so_number' => 'sometimes|string|max:255|unique:sales_orders,so_number',
            'reference_no' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|in:p,d', // pickup or delivery
            'date' => 'sometimes|date',
            'estimated_shipment_date' => 'sometimes|nullable|date',
            'payment_term_id' => 'sometimes|nullable|exists:payment_terms,id',
            'remarks' => 'sometimes|nullable|string|max:1000',
            'tax_rate' => 'sometimes|numeric|min:0',
            'user_id' => 'sometimes|exists:users,id',
        ];
    }
}
