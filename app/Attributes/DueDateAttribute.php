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
 *  *  Last modified: 11/12/24, 10:01 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Attributes;

use Illuminate\Support\Carbon;

trait DueDateAttribute
{
    public function getDueDateAttribute($value): ?string
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDueDateAttribute($value): void
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }
}
