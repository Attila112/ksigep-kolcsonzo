<?php

namespace Database\Factories;

use App\Models\BatterySystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatterySystem>
 */
class BatterySystemFactory extends Factory
{
    protected $model = BatterySystem::class;

    public function definition(): array
    {
        return [
            'manufacturer' => fake()->unique()->company(),
            'name' => 'Test System ' . fake()->unique()->numberBetween(1, 999999),
            'voltage' => fake()->randomElement([
                12.00,
                18.00,
                20.00,
                36.00,
            ]),
            'active' => true,
        ];
    }

    public function makita(): static
    {
        return $this->state(fn () => [
            'manufacturer' => 'Makita',
            'name' => 'LXT 18V',
            'voltage' => 18.00,
        ]);
    }

    public function parkside(): static
    {
        return $this->state(fn () => [
            'manufacturer' => 'Parkside',
            'name' => 'X20V Team',
            'voltage' => 20.00,
        ]);
    }

    public function bosch(): static
    {
        return $this->state(fn () => [
            'manufacturer' => 'Bosch',
            'name' => 'Professional 18V',
            'voltage' => 18.00,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }
}