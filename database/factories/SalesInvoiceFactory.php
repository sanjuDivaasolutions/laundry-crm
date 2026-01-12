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
 *  *  Last modified: 10/12/24, 6:18 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace Database\Factories;

use App\Models\Buyer;
use App\Models\Warehouse;
use App\Services\UtilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class SalesInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $config = [
            'table'  => 'sales_invoices',
            'field'  => 'invoice_number',
            'prefix' => 'SI-',
            'length' => 10,
        ];
        $code = UtilityService::generateCode($config);

        $buyer = Buyer::query()->inRandomOrder()->first();

        return [
            'company_id'      => 1,
            'invoice_number'  => $code,
            'reference_no'    => $this->faker->text,
            'type'            => 'p',
            'order_type'      => 'product',
            'date'            => Carbon::now()->format(config('project.date_format')),
            'due_date'        => Carbon::now()->format(config('project.date_format')),
            'remark'          => $this->faker->text,
            'sub_total'       => 0,
            'tax_rate'        => 5,
            'tax_total'       => 0,
            'grand_total'     => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
            'buyer_id'        => $buyer->id,
            'payment_term_id' => $buyer->payment_term_id,
            'warehouse_id'    => Warehouse::query()->inRandomOrder()->first()->id,
            'user_id'         => 1,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
