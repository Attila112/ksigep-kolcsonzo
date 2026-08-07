<?php

namespace Tests\Unit\Models;

use App\Models\BatteryItem;
use App\Models\BatterySystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatteryItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_battery_system(): void
    {
        $system = BatterySystem::factory()->create();

        $item = BatteryItem::factory()
            ->for($system, 'batterySystem')
            ->create();

        $this->assertTrue(
            $item->batterySystem->is($system)
        );
    }

    public function test_default_factory_item_is_available_battery(): void
    {
        $item = BatteryItem::factory()->create();

        $this->assertSame(
            BatteryItem::TYPE_BATTERY,
            $item->type
        );

        $this->assertSame(
            BatteryItem::STATUS_AVAILABLE,
            $item->status
        );
    }

    public function test_charger_factory_state_creates_charger(): void
    {
        $item = BatteryItem::factory()
            ->charger()
            ->create();

        $this->assertSame(
            BatteryItem::TYPE_CHARGER,
            $item->type
        );

        $this->assertStringStartsWith(
            'CHR-',
            $item->inventory_code
        );
    }

    public function test_battery_factory_state_creates_battery(): void
    {
        $item = BatteryItem::factory()
            ->battery()
            ->create();

        $this->assertSame(
            BatteryItem::TYPE_BATTERY,
            $item->type
        );

        $this->assertStringStartsWith(
            'BAT-',
            $item->inventory_code
        );
    }

    public function test_serial_number_can_be_null(): void
    {
        $item = BatteryItem::factory()->create([
            'serial_number' => null,
        ]);

        $this->assertNull($item->serial_number);
    }

    public function test_maintenance_state_sets_expected_status(): void
    {
        $item = BatteryItem::factory()
            ->maintenance()
            ->create();

        $this->assertSame(
            BatteryItem::STATUS_MAINTENANCE,
            $item->status
        );
    }

    public function test_constants_have_expected_values(): void
    {
        $this->assertSame(
            'BATTERY',
            BatteryItem::TYPE_BATTERY
        );

        $this->assertSame(
            'CHARGER',
            BatteryItem::TYPE_CHARGER
        );

        $this->assertSame(
            'AVAILABLE',
            BatteryItem::STATUS_AVAILABLE
        );

        $this->assertSame(
            'RENTED',
            BatteryItem::STATUS_RENTED
        );

        $this->assertSame(
            'INSPECTION',
            BatteryItem::STATUS_INSPECTION
        );

        $this->assertSame(
            'MAINTENANCE',
            BatteryItem::STATUS_MAINTENANCE
        );

        $this->assertSame(
            'DAMAGED',
            BatteryItem::STATUS_DAMAGED
        );

        $this->assertSame(
            'INACTIVE',
            BatteryItem::STATUS_INACTIVE
        );
    }
}