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
 *  *  Last modified: 11/12/24, 10:31 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace Database\Factories;

use App\Models\Company;
use App\Models\Inward;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InwardFactory extends Factory
{
    protected $model = Inward::class;

    public function definition(): array
    {
        return [
            'invoice_number' => $this->faker->word(),
            'reference_no'   => $this->faker->word(),
            'date'           => Carbon::now(),
            'remark'         => $this->faker->word(),
            'currency_rate'  => $this->faker->randomFloat(),
            'sub_total'      => $this->faker->randomFloat(),
            'tax_total'      => $this->faker->randomFloat(),
            'tax_rate'       => $this->faker->randomFloat(),
            'grand_total'    => $this->faker->randomFloat(),
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),

            'company_id'   => Company::factory(),
            'supplier_id'  => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id'      => User::factory(),
        ];
    }
}
