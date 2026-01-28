<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePriceFactory extends Factory
{
    protected $model = ServicePrice::class;

    public function definition(): array
    {
        $tenantId = Tenant::factory();

        return [
            'tenant_id' => $tenantId,
            'item_id' => Item::factory()->state(['tenant_id' => $tenantId]),
            'service_id' => Service::factory()->state(['tenant_id' => $tenantId]),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'is_active' => true,
        ];
    }
}
