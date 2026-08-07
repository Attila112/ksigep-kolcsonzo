<?php

namespace Tests\Unit\Models;

use App\Models\BatteryItem;
use App\Models\BatterySystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatterySystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_many_battery_items(): void
    {
        $system = BatterySystem::factory()->create();

        BatteryItem::factory()
            ->count(3)
            ->for($system, 'batterySystem')
            ->create();

        $this->assertCount(
            3,
            $system->fresh()->items
        );
    }

    public function test_active_is_cast_to_boolean(): void
    {
        $system = BatterySystem::factory()->create([
            'active' => 1,
        ]);

        $this->assertIsBool($system->active);
        $this->assertTrue($system->active);
    }

    public function test_voltage_is_cast_to_decimal_string(): void
    {
        $system = BatterySystem::factory()->create([
            'voltage' => 18,
        ]);

        $this->assertSame(
            '18.00',
            $system->voltage
        );
    }

    public function test_inactive_factory_state_sets_system_inactive(): void
    {
        $system = BatterySystem::factory()
            ->inactive()
            ->create();

        $this->assertFalse($system->active);
    }
}