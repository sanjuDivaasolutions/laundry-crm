<?php

namespace Database\Factories;

use App\Enums\OrderStatusTypeEnum;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusHistoryFactory extends Factory
{
    protected $model = OrderStatusHistory::class;

    public function definition(): array
    {
        $tenantId = Tenant::factory();
        
        return [
            'tenant_id' => $tenantId,
            'order_id' => Order::factory()->state(['tenant_id' => $tenantId]),
            'status_type' => $this->faker->randomElement(OrderStatusTypeEnum::cases()),
            'old_status_id' => $this->faker->numberBetween(1, 5),
            'new_status_id' => $this->faker->numberBetween(6, 10),
            'changed_by_employee_id' => 1,
            'remarks' => $this->faker->optional()->sentence,
            'changed_at' => now(),
        ];
    }
}
