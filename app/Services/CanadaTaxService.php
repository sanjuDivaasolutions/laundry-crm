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
 *  *  Last modified: 12/02/25, 4:31 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\State;
use App\Models\TaxRate;

class CanadaTaxService
{
    public static function getTaxesByState($stateId)
    {
        return TaxRate::query()
            ->where('state_id', $stateId)
            ->orderBy('priority')
            ->get();
    }

    public static function getDefaultStateObject()
    {
        return State::query()->select('id', 'name')->find(1);
    }
}
