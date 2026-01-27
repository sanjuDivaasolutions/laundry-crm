<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->words(2, true),
            'code' => 'ITM-'. $this->faker->unique()->numerify('####'),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'display_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
