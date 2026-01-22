<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'customer_code' => 'CUST-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->name,
            'phone' => $this->faker->unique()->phoneNumber,
            'address' => $this->faker->address,
            'is_active' => true,
        ];
    }
}
