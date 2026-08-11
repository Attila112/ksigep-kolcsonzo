<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        $unique = fake()->unique()->numberBetween(1, 999999);

        return [
            'product_id' => Product::factory(),
            'inventory_code' => sprintf(
                'INV-%06d',
                $unique
            ),
            'serial_number' => null,
            'status' => 'AVAILABLE',
            'admin_note' => null,
        ];
    }

    public function available(): static
    {
        return $this->state(fn() => [
            'status' => 'AVAILABLE',
        ]);
    }

    public function rented(): static
    {
        return $this->state(fn() => [
            'status' => 'RENTED',
        ]);
    }

    public function inspection(): static
    {
        return $this->state(fn() => [
            'status' => 'INSPECTION',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn() => [
            'status' => 'MAINTENANCE',
        ]);
    }

    public function damaged(): static
    {
        return $this->state(fn() => [
            'status' => 'DAMAGED',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => [
            'status' => 'INACTIVE',
        ]);
    }
}
