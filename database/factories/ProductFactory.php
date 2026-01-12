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
 *  *  Last modified: 16/10/24, 5:44 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use App\Services\UtilityService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $config = [
            'table'  => 'products',
            'field'  => 'code',
            'prefix' => 'INV-',
            'length' => 10,
        ];
        $code = UtilityService::generateCode($config);


        return [
            'code'          => $code,
            'type'          => $this->faker->randomElement(['product']),
            'name'          => $this->faker->realText(10),
            'sku'           => $this->faker->unique()->word,
            'description'   => $this->faker->text,
            'active'        => 1,
            'manufacturer'  => $this->faker->company,
            'is_returnable' => 1,
            'supplier_id'   => Supplier::query()->inRandomOrder()->first()->id,
            'user_id'       => 1,
            'unit_01_id'    => 1,
            'unit_02_id'    => 1,
            'category_id'   => Category::query()->inRandomOrder()->first()->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
    }
}
