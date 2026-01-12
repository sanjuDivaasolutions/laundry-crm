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
use Illuminate\Support\Facades\DB;

class TaxClassesSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('tax_classes')->insert([
            ['name' => 'GST', 'description' => 'Goods and Services Tax', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HST', 'description' => 'Harmonized Sales Tax', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PST', 'description' => 'Provincial Sales Tax', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'QST', 'description' => 'Quebec Sales Tax', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
