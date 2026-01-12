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
 *  *  Last modified: 12/12/24, 4:38 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseService
{
    /**
     * @throws \Exception
     */
    public static function executeTransaction(callable $callback)
    {
        try {
            DB::beginTransaction();
            $result = $callback();
            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transaction failed: '.$e->getMessage(), ['exception' => $e]);
            abort(500, 'An error occurred during the transaction.');
        }
    }
}
