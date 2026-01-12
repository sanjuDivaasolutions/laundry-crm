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
 *  *  Last modified: 21/01/25, 4:17 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Attributes;

use Illuminate\Support\Carbon;

trait DateAttribute
{
    public function getDateAttribute($value): ?string
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }
}
