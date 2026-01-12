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

namespace App\Traits;

use DateTimeInterface;

trait SerializeDate
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
