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
 *  *  Last modified: 11/02/25, 6:28 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\CanadaTaxation;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountryTableSeeder extends Seeder
{
    public function run()
    {
        $country = [
            [
                'id'         => 1,
                'name'       => 'Canada',
                'active'     => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        Country::insert($country);

        /*Country::factory()
            ->has(State::factory()->has(City::factory()->count(5),'cities')->count(5), 'states')
            ->count(10)->create();*/
    }
}
