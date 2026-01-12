<?php

/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 14/10/24, 5:38 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required'],
            'name' => ['required'],
            'address_1' => ['required'],
            'address_2' => ['required'],
            'active' => ['boolean'],
            'user_id' => ['required', 'exists:users'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
