<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ContactAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'  =>  fake()->unique()->name(),
            'address_1' => fake()->address(),
            'address_2' => fake()->address(),
            'country_id' => 1,
            'state_id' => 1,
            'city_id' => 1,
            'postal_code' => '12345',
            'phone' => fake()->phoneNumber(),
            'created_at'=>now(),
            'updated_at'=>now(),
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
