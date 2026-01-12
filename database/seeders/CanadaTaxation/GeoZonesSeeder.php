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
 *  *  Last modified: 12/02/25, 4:13 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\CanadaTaxation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GeoZonesSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('geo_zones')->insert([
            ['name' => 'Canada - GST Zone', 'description' => 'Applies to all provinces', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Canada - HST Zone', 'description' => 'Applies to specific provinces', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Canada - PST/QST Zone', 'description' => 'Applies to certain provinces with additional taxes', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
