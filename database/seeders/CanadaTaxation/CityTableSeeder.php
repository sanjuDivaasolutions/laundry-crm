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
 *  *  Last modified: 11/02/25, 6:26 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\CanadaTaxation;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CityTableSeeder extends Seeder
{
    public function run()
    {
        $cities = [
            // Alberta (state_id = 1)
            ['name' => 'Calgary', 'state_id' => 1, 'active' => 1],
            ['name' => 'Edmonton', 'state_id' => 1, 'active' => 1],
            ['name' => 'Red Deer', 'state_id' => 1, 'active' => 1],

            // British Columbia (state_id = 2)
            ['name' => 'Vancouver', 'state_id' => 2, 'active' => 1],
            ['name' => 'Victoria', 'state_id' => 2, 'active' => 1],
            ['name' => 'Surrey', 'state_id' => 2, 'active' => 1],

            // Manitoba (state_id = 3)
            ['name' => 'Winnipeg', 'state_id' => 3, 'active' => 1],
            ['name' => 'Brandon', 'state_id' => 3, 'active' => 1],

            // New Brunswick (state_id = 4)
            ['name' => 'Fredericton', 'state_id' => 4, 'active' => 1],
            ['name' => 'Saint John', 'state_id' => 4, 'active' => 1],

            // Newfoundland and Labrador (state_id = 5)
            ['name' => 'St. John\'s', 'state_id' => 5, 'active' => 1],

            // Northwest Territories (state_id = 6)
            ['name' => 'Yellowknife', 'state_id' => 6, 'active' => 1],

            // Nova Scotia (state_id = 7)
            ['name' => 'Halifax', 'state_id' => 7, 'active' => 1],
            ['name' => 'Sydney', 'state_id' => 7, 'active' => 1],

            // Nunavut (state_id = 8)
            ['name' => 'Iqaluit', 'state_id' => 8, 'active' => 1],

            // Ontario (state_id = 9)
            ['name' => 'Toronto', 'state_id' => 9, 'active' => 1],
            ['name' => 'Ottawa', 'state_id' => 9, 'active' => 1],
            ['name' => 'Mississauga', 'state_id' => 9, 'active' => 1],
            ['name' => 'Brampton', 'state_id' => 9, 'active' => 1],

            // Prince Edward Island (state_id = 10)
            ['name' => 'Charlottetown', 'state_id' => 10, 'active' => 1],

            // Quebec (state_id = 11)
            ['name' => 'Montreal', 'state_id' => 11, 'active' => 1],
            ['name' => 'Quebec City', 'state_id' => 11, 'active' => 1],
            ['name' => 'Laval', 'state_id' => 11, 'active' => 1],

            // Saskatchewan (state_id = 12)
            ['name' => 'Regina', 'state_id' => 12, 'active' => 1],
            ['name' => 'Saskatoon', 'state_id' => 12, 'active' => 1],

            // Yukon (state_id = 13)
            ['name' => 'Whitehorse', 'state_id' => 13, 'active' => 1],
        ];

        $now = Carbon::now();

        //merge created_at and updated_at
        foreach ($cities as $key => $city) {
            $cities[$key]['created_at'] = $now;
            $cities[$key]['updated_at'] = $now;
        }

        DB::table('cities')->insert($cities);
    }
}
