<?php
return [
    'defaults' => [
        'tax_percentage' => env('DEFAULT_TAX_PERCENTAGE', 13),
    ],
    'accountType'   =>  [
        'ids'   => [
            'bank'              =>  1,
            'bankOcc'           =>  2,
            'bankOd'            =>  3,
            'cashInHand'        =>  6,
            'indirectExpenses'  =>  18,
            'purchase'          =>  25,
            'sales'             =>  28,
            'sundryCreditors'   =>  32,
            'sundryDebtors'     =>  33,
        ],
    ],
    'account'   =>  [
        'ids'   => [
            'cashInHand'        =>  1,
            'purchase'          =>  2,
            'sales'             =>  3,
        ],
    ]
];
