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
 *  *  Last modified: 21/01/25, 9:59 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Attributes;

trait StatusLabelAttribute
{
    public function getStatusLabelAttribute()
    {
        return collect(static::STATUS_SELECT)->firstWhere('value', $this->status)['label'] ?? '';
    }
}
