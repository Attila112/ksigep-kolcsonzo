<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $unique = fake()->unique()->numberBetween(1, 999999);

        return [
            'category_id' => Category::factory(),
            'name' => 'Teszt termék ' . $unique,
            'sku' => 'TEST-' . $unique,
            'inventory_prefix' => 'T' . $unique,
            'description' => 'Teszt termék leírása.',
            'image_path' => null,
            'battery_system_id' => null,
            'required_batteries' => 0,
            'required_chargers' => 0,
            'price_per_day' => 5000,
            'deposit' => 10000,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }
}