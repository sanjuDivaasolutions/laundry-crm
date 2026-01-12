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
 *  *  Last modified: 17/01/25, 8:01 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quotation_id' => ['required', 'exists:quotations'],
            'product_id'   => ['required', 'exists:products'],
            'unit_id'      => ['required', 'exists:units'],
            'title'        => ['required'],
            'sku'          => ['required'],
            'rate'         => ['required', 'numeric'],
            'quantity'     => ['required', 'numeric'],
            'amount'       => ['required', 'numeric'],
            'remark'       => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
