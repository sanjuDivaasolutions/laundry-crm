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
 *  *  Last modified: 16/01/25, 8:55 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasAdvancedFilter;

    protected $fillable = [
        'name',
        'active',
    ];

    protected array $filterable = [
        'id',
        'name',
        'active',
    ];

    protected array $orderable = [
        'id',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
