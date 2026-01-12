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
 *  *  Last modified: 07/01/25, 5:09 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Traits;

use App\Services\AuthService;

trait CompanyScopeTrait
{
    public function scopeCompany($q): void
    {
        $companyId = AuthService::getCompanyId();
        if ($companyId) {
            $q->where('company_id', $companyId);
        }
    }
}
