<?php

namespace Tests\Feature\Admin\Battery;

use App\Models\BatteryItem;
use App\Models\BatterySystem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBatteryShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_battery_item_details(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        $batteryItem = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'BAT-001',
            'type' => 'BATTERY',
            'status' => 'AVAILABLE',
            'serial_number' => 'SN-BAT-123456',
            'admin_note' => 'Teszt akkumulátor.',
        ]);

        $response = $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'battery_item.id',
                $batteryItem->id
            )
            ->assertJsonPath(
                'battery_item.inventory_code',
                'BAT-001'
            )
            ->assertJsonPath(
                'battery_item.type',
                'BATTERY'
            )
            ->assertJsonPath(
                'battery_item.status',
                'AVAILABLE'
            )
            ->assertJsonPath(
                'battery_item.serial_number',
                'SN-BAT-123456'
            )
            ->assertJsonPath(
                'battery_item.admin_note',
                'Teszt akkumulátor.'
            )
            ->assertJsonPath(
                'battery_item.battery_system.id',
                $batterySystem->id
            )
            ->assertJsonPath(
                'battery_item.battery_system.manufacturer',
                'Makita'
            )
            ->assertJsonPath(
                'battery_item.battery_system.name',
                $batterySystem->name
            )
            ->assertJsonPath(
                'battery_item.battery_system.voltage',
                $batterySystem->voltage
            );
    }

    public function test_admin_can_view_charger_details(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        $charger = BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'CHG-001',
            'type' => 'CHARGER',
            'status' => 'AVAILABLE',
        ]);

        $this->getJson(
            "/api/admin/battery-items/{$charger->id}"
        )
            ->assertOk()
            ->assertJsonPath(
                'battery_item.id',
                $charger->id
            )
            ->assertJsonPath(
                'battery_item.type',
                'CHARGER'
            );
    }

    public function test_admin_can_view_inactive_battery_item(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $batteryItem = BatteryItem::factory()
            ->inactive()
            ->create();

        $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}"
        )
            ->assertOk()
            ->assertJsonPath(
                'battery_item.id',
                $batteryItem->id
            )
            ->assertJsonPath(
                'battery_item.status',
                'INACTIVE'
            );
    }

    public function test_customer_cannot_view_admin_battery_item_details(): void
    {
        $customer = User::factory()
            ->customer()
            ->create();

        Sanctum::actingAs($customer);

        $batteryItem = BatteryItem::factory()->create();

        $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}"
        )
            ->assertForbidden();
    }

    public function test_guest_cannot_view_admin_battery_item_details(): void
    {
        $batteryItem = BatteryItem::factory()->create();

        $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}"
        )
            ->assertUnauthorized();
    }

    public function test_admin_receives_not_found_for_missing_battery_item(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $this->getJson(
            '/api/admin/battery-items/999999'
        )
            ->assertNotFound();
    }
}