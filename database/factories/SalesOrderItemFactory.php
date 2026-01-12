<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SalesOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $product = Product::query()->with(['prices'])->inRandomOrder()->first();

        $price = $product->prices->first();
        $quantity = $this->faker->numberBetween(1, 10);

        $rate = $price ? $price->sale_price : 0;

        return [

            'product_id' => $product->id,
            'quantity' => $quantity,
            'rate' => $rate,
            'amount' => $rate * $quantity,
            'created_at' => now(),
            'updated_at' => now(),
            'sku' => $product->sku,
            'description' => $product->description,
            'unit_id' => $product->unit_01_id,
        ];
    }
}
