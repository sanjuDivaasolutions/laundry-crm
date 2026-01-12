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
 *  *  Last modified: 12/02/25, 4:25 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\CanadaTaxation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxRatesSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        //['geo_zone_id' => 1, 'state_id' => 1, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],
        $taxRates = [
            //Alberta
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 1, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //British Columbia
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 2, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 2, 'geo_zone_id' => 1, 'state_id' => 2, 'name' => 'PST', 'rate' => 7.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Manitoba
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 3, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 2, 'geo_zone_id' => 1, 'state_id' => 3, 'name' => 'PST', 'rate' => 7.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //New Brunswick
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 4, 'name' => 'HST', 'rate' => 15.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Newfoundland and Labrador
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 5, 'name' => 'HST', 'rate' => 15.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Northwest Territories
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 6, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Nova Scotia
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 7, 'name' => 'HST', 'rate' => 15.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Nunavut
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 8, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Ontario
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 9, 'name' => 'HST', 'rate' => 13.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Prince Edward Island
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 10, 'name' => 'HST', 'rate' => 15.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Quebec
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 11, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 2, 'geo_zone_id' => 1, 'state_id' => 11, 'name' => 'QST', 'rate' => 9.975, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Saskatchewan
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 12, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 2, 'geo_zone_id' => 1, 'state_id' => 12, 'name' => 'PST', 'rate' => 6.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

            //Yukon
            ['priority' => 1, 'geo_zone_id' => 1, 'state_id' => 13, 'name' => 'GST', 'rate' => 5.00, 'type' => 'P', 'created_at' => $now, 'updated_at' => $now],

        ];

        DB::table('tax_rates')->insert($taxRates);
    }
}
