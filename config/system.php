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
 *  *  Last modified: 16/01/25, 11:53 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

return [
    'auth'                       => [
        'admin'             => 'admin',
        'admin_role_id'     => [1],
        'non_admin_role_id' => [2],
        'hidden_role_id'    => [], //[] for none
    ],
    'model'                      => [
        'cache' => [
            'enabled' => false,
            'prefix'  => 'model-keys',
            'ttl'     => 3600, //1 hour
        ],
    ],
    'query'                      => [
        'limit'  => 10,
        'users'  => [
            'limit' => 10,
        ],
        'search' => [
            'type'               => 'anywhere', //start, anywhere, end
            'key_expire_timeout' => 300, //5 minutes
            'key_preserve_list'  => [
                'yes-nos',
                'units',
                'payment-terms',
                'currencies',
                'countries',
                'states',
                'cities',
                'warehouses',
                'features',
            ],
            'preload_options'    => [
                'yes-nos',
                'units',
                'payment-terms',
                'currencies',
                'warehouses',
                'features',
            ],
        ],
    ],
    'defaults'                   => [
        'currency'           => [
            'id'     => 1,
            'code'   => 'USD',
            'symbol' => '$',
        ],
        'service_company'    => [
            'id' => 2,
        ],
        'company'            => [
            'id' => 1,
        ],
        'warehouse'          => [
            'id' => 1,
        ],
        'state'              => [
            'id' => 1,
        ],
        'language'           => [
            'id'        => 1,
            'protected' => [1, 2],
        ],
        'language_group'     => [
            'id'   => 1,
            'name' => 'general',
        ],
        'department'         => [
            'gz' => [
                'id'   => 4,
                'name' => 'GZ',
            ],
        ],
        'opening_stock_date' => '01/01/2024',
    ],
    'restricted'                 => [
        'company' => [1, 2, 3],
    ],
    'reports'                    => [
        'profit_loss' => [
            'use_cogs_calculation' => true, // Set to false to revert to original purchase-based calculation
        ],
    ],
    'currency'                   => [
        'usd' => 1,
        'cny' => 2,
    ],
    'account'                    => [
        'buyer'          => 1,
        'customer'       => 1,
        'supplier'       => 2,
        'order_expenses' => 4,
        'sales'          => 5,
        'purchase'       => 6,
        'commission'     => 7,
    ],
    'opening_balance_account_id' => 29,
    'prefix'                     => [
        'sample_order' => 'YDG',
        'inquiry'      => 'INQ',
    ],
];
