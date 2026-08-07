<?php

namespace Database\Factories;

use App\Models\BatteryItem;
use App\Models\BatterySystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatteryItem>
 */
class BatteryItemFactory extends Factory
{
    protected $model = BatteryItem::class;

    public function definition(): array
    {
        return [
            'battery_system_id' => BatterySystem::factory(),

            'inventory_code' => sprintf(
                'BAT-%04d',
                fake()->unique()->numberBetween(1, 9999)
            ),

            'type' => BatteryItem::TYPE_BATTERY,

            'serial_number' => strtoupper(
                fake()->unique()->bothify('SN-########')
            ),

            'status' => BatteryItem::STATUS_AVAILABLE,

            'admin_note' => null,
        ];
    }

    /**
     * Akkumulátor példány.
     */
    public function battery(): static
    {
        return $this->state(fn () => [
            'type' => BatteryItem::TYPE_BATTERY,
            'inventory_code' => sprintf(
                'BAT-%04d',
                fake()->unique()->numberBetween(1, 9999)
            ),
        ]);
    }

    /**
     * Töltő példány.
     */
    public function charger(): static
    {
        return $this->state(fn () => [
            'type' => BatteryItem::TYPE_CHARGER,
            'inventory_code' => sprintf(
                'CHR-%04d',
                fake()->unique()->numberBetween(1, 9999)
            ),
        ]);
    }

    public function available(): static
    {
        return $this->state(fn () => [
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);
    }

    public function rented(): static
    {
        return $this->state(fn () => [
            'status' => BatteryItem::STATUS_RENTED,
        ]);
    }

    public function inspection(): static
    {
        return $this->state(fn () => [
            'status' => BatteryItem::STATUS_INSPECTION,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn () => [
            'status' => BatteryItem::STATUS_MAINTENANCE,
        ]);
    }

    public function damaged(): static
    {
        return $this->state(fn () => [
            'status' => BatteryItem::STATUS_DAMAGED,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => BatteryItem::STATUS_INACTIVE,
        ]);
    }
}