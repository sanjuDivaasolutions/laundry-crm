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
 *  *  Last modified: 16/01/25, 11:16 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use App\Models\Expense;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Expense */
class ExpenseListResource extends JsonResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);
        $currencySign = '$';

        $result['sub_total_label'] = $currencySign.number_format($result['sub_total'], 2);
        $result['tax_total_label'] = $currencySign.number_format($result['tax_total'], 2);
        $result['grand_total_label'] = $currencySign.number_format($result['grand_total'], 2);

        return $result;
    }
}
