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
 *  *  Last modified: 12/02/25, 4:14 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\CanadaTaxation;

use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StateTableSeeder extends Seeder
{
    public function run()
    {
        $states = [
            ['name' => 'Alberta', 'code' => 'AB', 'country_id' => 1, 'active' => 1], // Adjust country_id accordingly
            ['name' => 'British Columbia', 'code' => 'BC', 'country_id' => 1, 'active' => 1],
            ['name' => 'Manitoba', 'code' => 'MB', 'country_id' => 1, 'active' => 1],
            ['name' => 'New Brunswick', 'code' => 'NB', 'country_id' => 1, 'active' => 1],
            ['name' => 'Newfoundland and Labrador', 'code' => 'NL', 'country_id' => 1, 'active' => 1],
            ['name' => 'Northwest Territories', 'code' => 'NT', 'country_id' => 1, 'active' => 1],
            ['name' => 'Nova Scotia', 'code' => 'NS', 'country_id' => 1, 'active' => 1],
            ['name' => 'Nunavut', 'code' => 'NU', 'country_id' => 1, 'active' => 1],
            ['name' => 'Ontario', 'code' => 'ON', 'country_id' => 1, 'active' => 1],
            ['name' => 'Prince Edward Island', 'code' => 'PE', 'country_id' => 1, 'active' => 1],
            ['name' => 'Quebec', 'code' => 'QC', 'country_id' => 1, 'active' => 1],
            ['name' => 'Saskatchewan', 'code' => 'SK', 'country_id' => 1, 'active' => 1],
            ['name' => 'Yukon', 'code' => 'YT', 'country_id' => 1, 'active' => 1],
        ];

        $now = Carbon::now();

        $id = 0;
        foreach ($states as $key => $state) {
            $id++;
            $states[$key]['id'] = $id;
            $states[$key]['created_at'] = $now;
            $states[$key]['updated_at'] = $now;
        }

        State::insert($states);
    }
}
