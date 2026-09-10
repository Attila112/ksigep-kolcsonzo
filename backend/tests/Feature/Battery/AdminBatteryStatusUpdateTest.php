<?php

namespace Tests\Feature\Battery;

use App\Models\BatteryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBatteryStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_battery_item_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_MAINTENANCE,
                'admin_note' => 'Karbantartás szükséges.',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'battery_item.status',
                BatteryItem::STATUS_MAINTENANCE
            )
            ->assertJsonPath(
                'battery_item.admin_note',
                'Karbantartás szükséges.'
            );

        $this->assertDatabaseHas('battery_items', [
            'id' => $batteryItem->id,
            'status' => BatteryItem::STATUS_MAINTENANCE,
            'admin_note' => 'Karbantartás szükséges.',
        ]);

        $this->assertDatabaseHas(
            'battery_status_histories',
            [
                'battery_item_id' => $batteryItem->id,
                'changed_by_user_id' => $admin->id,
                'from_status' => BatteryItem::STATUS_AVAILABLE,
                'to_status' => BatteryItem::STATUS_MAINTENANCE,
                'note' => 'Karbantartás szükséges.',
            ]
        );
    }

    public function test_admin_can_set_battery_item_to_available_without_note(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_INSPECTION,
            'admin_note' => 'Korábbi megjegyzés.',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_AVAILABLE,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'battery_item.status',
                BatteryItem::STATUS_AVAILABLE
            )
            ->assertJsonPath(
                'battery_item.admin_note',
                null
            );
    }

    public function test_admin_note_is_required_when_status_is_not_available(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_DAMAGED,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'admin_note',
            ]);
    }

    public function test_rented_cannot_be_selected_as_manual_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_RENTED,
                'admin_note' => 'Kiadás.',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'status',
            ]);
    }

    public function test_currently_rented_battery_item_cannot_be_manually_changed(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_AVAILABLE,
            ]
        );

        $response->assertUnprocessable();

        $this->assertDatabaseHas('battery_items', [
            'id' => $batteryItem->id,
            'status' => BatteryItem::STATUS_RENTED,
        ]);
    }

    public function test_customer_cannot_update_battery_item_status(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
        ]);

        $batteryItem = BatteryItem::factory()->create();

        Sanctum::actingAs($customer);

        $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_AVAILABLE,
            ]
        )->assertForbidden();
    }

    public function test_guest_cannot_update_battery_item_status(): void
    {
        $batteryItem = BatteryItem::factory()->create();

        $this->patchJson(
            "/api/admin/battery-items/{$batteryItem->id}/status",
            [
                'status' => BatteryItem::STATUS_AVAILABLE,
            ]
        )->assertUnauthorized();
    }
}