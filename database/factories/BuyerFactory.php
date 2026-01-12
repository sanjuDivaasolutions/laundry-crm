<?php

namespace Database\Factories;

use App\Models\Buyer;
use App\Models\Currency;
use App\Services\UtilityService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class BuyerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->company();
        $paymentTerm = \App\Models\PaymentTerm::inRandomOrder()->first();


        $billingAddress = \App\Models\ContactAddress::factory()->create();
        $shippingAddress = \App\Models\ContactAddress::factory()->create();

        $config = [
            'table' =>  'buyers',
            'field' =>  'code',
            'prefix'=>  'BUY-',
            'length'=>  10,
        ];
        $code = UtilityService::generateCode($config);



        $currencies = Currency::all();

        return [
            'code'  =>  $code,
            'company_id'   => 1, // Default to first company
            'currency_id'   =>  $currencies->random()->id,
            'name' => $name,
            'display_name' => $name,
            'payment_term_id' => $paymentTerm->id,
            'billing_address_id' => $billingAddress->id,
            'shipping_address_id' => $shippingAddress->id,
            'active' => 1,
            'remarks' => fake()->sentence(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
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
