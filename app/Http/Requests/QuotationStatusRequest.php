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
 *  *  Last modified: 21/01/25, 9:55 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date'         => ['required'],
            'active'       => ['boolean'],
            'status'       => ['required'],
            'remark'       => ['nullable'],
            'quotation_id' => ['required', 'exists:quotations'],
            'user_id'      => ['required', 'exists:users'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
