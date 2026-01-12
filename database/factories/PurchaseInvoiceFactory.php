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
 *  *  Last modified: 16/10/24, 4:14 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\UtilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PurchaseInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $config = [
            'table'  => 'purchase_invoices',
            'field'  => 'invoice_number',
            'prefix' => 'PI-',
            'length' => 10,
        ];
        $code = UtilityService::generateCode($config);

        return [
            'company_id'        => 1,
            'purchase_order_id' => PurchaseOrder::query()->inRandomOrder()->first(),
            'invoice_number'    => $code,
            'date'              => Carbon::now()->format(config('project.date_format')),
            'due_date'          => Carbon::now()->format(config('project.date_format')),
            'remark'            => $this->faker->text,
            'type'              => 'p',
            'reference_no'      => $this->faker->text,
            'sub_total'         => 1000,
            'tax_rate'          => 5,
            'tax_total'         => 50,
            'grand_total'       => 1050,
            'created_at'        => now(),
            'updated_at'        => now(),
            'supplier_id'       => Supplier::query()->inRandomOrder()->first()->id,
            'user_id'           => 1,
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
