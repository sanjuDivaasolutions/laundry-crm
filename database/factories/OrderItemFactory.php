<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 50);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'order_id' => Order::factory(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? 1,
            'item_name' => $this->faker->word,
            'service_name' => $this->faker->randomElement(['Wash', 'Dry Clean', 'Iron']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'barcode' => $this->faker->unique()->ean13,
            'notes' => $this->faker->sentence,
        ];
    }
}
