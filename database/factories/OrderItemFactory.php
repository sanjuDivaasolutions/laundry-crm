<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
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
            'category_id' => Category::factory(),
            'item_id' => Item::factory(),
            'service_id' => Service::factory(),
            'item_name' => $this->faker->word,
            'service_name' => $this->faker->randomElement(['Wash', 'Dry Clean', 'Iron']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'barcode' => $this->faker->unique()->ean13,
            'color' => $this->faker->safeColorName,
            'brand' => $this->faker->company,
            'defect_notes' => $this->faker->optional()->sentence,
            'notes' => $this->faker->sentence,
        ];
    }
}
