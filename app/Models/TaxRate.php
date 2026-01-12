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
 *  *  Last modified: 12/02/25, 4:30 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'priority',
        'type',
        'geo_zone_id',
        'state_id',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function getRateAttribute($value): float
    {
        return round($value, 2);
    }
}
