<?php

namespace Database\Factories;

use App\Enums\PaymentMethodEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $tenantId = Tenant::factory();

        return [
            'tenant_id' => $tenantId,
            'payment_number' => 'PAY-'.$this->faker->unique()->numberBetween(10000, 99999),
            'order_id' => Order::factory()->state(['tenant_id' => $tenantId]),
            'customer_id' => Customer::factory()->state(['tenant_id' => $tenantId]),
            'payment_date' => now(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'payment_method' => PaymentMethodEnum::Cash,
            'transaction_reference' => $this->faker->optional()->uuid,
            'notes' => $this->faker->sentence,
            'received_by_employee_id' => 1,
            'created_at' => now(),
        ];
    }
}
