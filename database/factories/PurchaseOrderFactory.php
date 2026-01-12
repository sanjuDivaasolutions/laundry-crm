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

use App\Models\PaymentTerm;
use App\Models\ShipmentMode;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\UtilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $config = [
            'table'  => 'purchase_orders',
            'field'  => 'po_number',
            'prefix' => 'PO-',
            'length' => 10,
        ];
        $code = UtilityService::generateCode($config);

        return [
            'po_number'               => $code,
            'company_id'              => 1,
            'date'                    => Carbon::now()->format(config('project.date_format')),
            'estimated_shipment_date' => Carbon::now()->format(config('project.date_format')),
            'remarks'                 => $this->faker->text,
            'freight_total'           => 0,
            'discount_type'           => 'p',
            'discount_total'          => 0,
            'discount_rate'           => 0,
            'sub_total'               => 1000,
            'tax_rate'                => 5,
            'tax_total'               => 50,
            'grand_total'             => 1050,
            'created_at'              => now(),
            'updated_at'              => now(),
            'supplier_id'             => Supplier::query()->inRandomOrder()->first()->id,
            'payment_term_id'         => PaymentTerm::query()->inRandomOrder()->first()->id,
            'shipment_mode_id'        => ShipmentMode::query()->inRandomOrder()->first()->id,
            'warehouse_id'            => Warehouse::query()->inRandomOrder()->first()->id,
            'user_id'                 => 1,
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
