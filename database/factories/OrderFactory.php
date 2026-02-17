<?php

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\ProcessingStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $tenantId = Tenant::factory();

        return [
            'tenant_id' => $tenantId,
            'order_number' => 'ORD-'.$this->faker->unique()->numberBetween(10000, 99999),
            'customer_id' => Customer::factory()->state(['tenant_id' => $tenantId]),
            'order_date' => now(),
            'promised_date' => now()->addDays(2),
            'total_items' => 0,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
            'payment_status' => PaymentStatusEnum::Unpaid,
            'processing_status_id' => fn () => ProcessingStatus::where('status_name', 'Pending')->first()?->id
                ?? ProcessingStatus::first()?->id
                ?? ProcessingStatus::create(['status_name' => 'Pending', 'display_order' => 1, 'is_active' => true])->id,
            'order_status_id' => fn () => OrderStatus::where('status_name', 'Open')->first()?->id
                ?? OrderStatus::first()?->id
                ?? OrderStatus::create(['status_name' => 'Open', 'display_order' => 1, 'is_active' => true])->id,
            'created_by_employee_id' => 1,
            'urgent' => false,
            'hanger_number' => null,
            'tax_rate' => 10.00,
            'tax_amount' => 0,
            'discount_type' => 'fixed',
        ];
    }
}
