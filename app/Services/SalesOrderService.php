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
 *  *  Last modified: 18/11/25, 12:05 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\Company;

class SalesOrderService
{
    public static function generateCode(Company $company, $field = 'so_number', $separator = '/', $digits = 4): string
    {
        $prefix = 'SO' . $separator . $company->code . $separator . date('y') . $separator;
        $length = strlen($prefix) + $digits;

        $where = [
            [$field, 'LIKE', $prefix . '%'],
        ];

        $config = [
            'table'  => 'sales_orders',
            'field'  => $field,
            'length' => $length,
            'prefix' => $prefix,
            'where'  => $where,
        ];
        
        return UtilityService::generateCode($config);
    }
}