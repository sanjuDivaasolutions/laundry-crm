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
 *  *  Last modified: 11/02/25, 6:13 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\CanadaTaxation;

use Illuminate\Database\Seeder;

class CanadaTaxationSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            TaxClassesSeeder::class,
            GeoZonesSeeder::class,
            CountryTableSeeder::class,
            StateTableSeeder::class,
            CityTableSeeder::class,
            TaxRatesSeeder::class,
        ]);
    }
}
