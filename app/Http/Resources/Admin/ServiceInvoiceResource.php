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
 *  *  Last modified: 18/11/24, 9:07 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceInvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
