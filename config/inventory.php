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
 *  *  Last modified: 10/12/24, 9:50 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

return [
    'defaults' => [
        'warehouse_id' => env('DEFAULT_WAREHOUSE_ID', 1),
        'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
    ],
    'reasons' => [
        'subtraction'  => ['sales', 'damage', 'transfer_out', 'pos_sale'],
        'addition'     => ['purchase', 'return', 'transfer_in'],
        'sales'        => 'sales',
        'pos_sale'     => 'POS Sale',
        'purchase'     => 'purchase',
        'damage'       => 'damage',
        'return'       => 'return',
        'transfer_in'  => 'transfer_in',
        'transfer_out' => 'transfer_out',
    ],
];

