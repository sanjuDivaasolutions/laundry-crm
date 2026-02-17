<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DeliverySchedule;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryScheduleFactory extends Factory
{
    protected $model = DeliverySchedule::class;

    public function definition(): array
    {
        $tenantId = Tenant::factory();

        return [
            'tenant_id' => $tenantId,
            'order_id' => Order::factory()->state(['tenant_id' => $tenantId]),
            'customer_id' => Customer::factory()->state(['tenant_id' => $tenantId]),
            'type' => $this->faker->randomElement(['pickup', 'delivery']),
            'scheduled_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            'scheduled_time' => $this->faker->time('H:i'),
            'address' => $this->faker->address,
            'notes' => $this->faker->optional()->sentence,
            'assigned_to_employee_id' => null,
            'status' => 'pending',
        ];
    }
}
