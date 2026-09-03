<?php

namespace Tests\Feature\Admin\Battery;

use App\Models\BatteryItem;
use App\Models\BatterySystem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBatteryListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_battery_items(): void
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
        ]);

        $response = $this->getJson(
            '/api/admin/battery-items'
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'battery_items')
            ->assertJsonPath(
                'battery_items.0.id',
                $batteryItem->id
            )
            ->assertJsonPath(
                'battery_items.0.inventory_code',
                'BAT-001'
            )
            ->assertJsonPath(
                'battery_items.0.type',
                'BATTERY'
            )
            ->assertJsonPath(
                'battery_items.0.status',
                'AVAILABLE'
            )
            ->assertJsonPath(
                'battery_items.0.battery_system.id',
                $batterySystem->id
            )
            ->assertJsonPath(
                'battery_items.0.battery_system.manufacturer',
                'Makita'
            );
    }

    public function test_admin_battery_list_includes_chargers(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'inventory_code' => 'CHG-001',
            'type' => 'CHARGER',
            'status' => 'AVAILABLE',
        ]);

        $this->getJson('/api/admin/battery-items')
            ->assertOk()
            ->assertJsonPath(
                'battery_items.0.type',
                'CHARGER'
            );
    }

    public function test_admin_battery_list_includes_all_statuses(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'status' => 'AVAILABLE',
        ]);

        BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'status' => 'MAINTENANCE',
        ]);

        BatteryItem::factory()->create([
            'battery_system_id' => $batterySystem->id,
            'status' => 'INACTIVE',
        ]);

        $response = $this->getJson(
            '/api/admin/battery-items'
        );

        $response
            ->assertOk()
            ->assertJsonCount(3, 'battery_items');

        $response->assertJsonFragment([
            'status' => 'AVAILABLE',
        ]);

        $response->assertJsonFragment([
            'status' => 'MAINTENANCE',
        ]);

        $response->assertJsonFragment([
            'status' => 'INACTIVE',
        ]);
    }

    public function test_customer_cannot_list_battery_items(): void
    {
        $customer = User::factory()
            ->customer()
            ->create();

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/battery-items')
            ->assertForbidden();
    }

    public function test_guest_cannot_list_battery_items(): void
    {
        $this->getJson('/api/admin/battery-items')
            ->assertUnauthorized();
    }

    public function test_admin_battery_list_returns_empty_array_when_no_items_exist(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/battery-items')
            ->assertOk()
            ->assertExactJson([
                'battery_items' => [],
            ]);
    }
}