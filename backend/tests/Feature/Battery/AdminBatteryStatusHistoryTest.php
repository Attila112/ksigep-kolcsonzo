<?php

namespace Tests\Feature\Battery;

use App\Models\BatteryItem;
use App\Models\BatteryStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBatteryStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_battery_item_status_history(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $changedBy = User::factory()->create([
            'name' => 'Teszt Admin',
        ]);

        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_MAINTENANCE,
        ]);

        BatteryStatusHistory::query()->create([
            'battery_item_id' => $batteryItem->id,
            'changed_by_user_id' => $changedBy->id,
            'from_status' => BatteryItem::STATUS_AVAILABLE,
            'to_status' => BatteryItem::STATUS_INSPECTION,
            'note' => 'Bevizsgálás szükséges.',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        BatteryStatusHistory::query()->create([
            'battery_item_id' => $batteryItem->id,
            'changed_by_user_id' => $changedBy->id,
            'from_status' => BatteryItem::STATUS_INSPECTION,
            'to_status' => BatteryItem::STATUS_MAINTENANCE,
            'note' => 'Karbantartásra küldve.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}/status-history"
        );

        $response
            ->assertOk()
            ->assertJsonCount(
                2,
                'status_history'
            )
            ->assertJsonPath(
                'battery_item.id',
                $batteryItem->id
            )
            ->assertJsonPath(
                'battery_item.inventory_code',
                $batteryItem->inventory_code
            )
            ->assertJsonPath(
                'status_history.0.from_status',
                BatteryItem::STATUS_INSPECTION
            )
            ->assertJsonPath(
                'status_history.0.to_status',
                BatteryItem::STATUS_MAINTENANCE
            )
            ->assertJsonPath(
                'status_history.0.note',
                'Karbantartásra küldve.'
            )
            ->assertJsonPath(
                'status_history.0.changed_by.id',
                $changedBy->id
            )
            ->assertJsonPath(
                'status_history.0.changed_by.name',
                'Teszt Admin'
            );
    }

    public function test_status_history_is_returned_newest_first(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create();

        $olderHistory = BatteryStatusHistory::query()->create([
            'battery_item_id' => $batteryItem->id,
            'changed_by_user_id' => null,
            'from_status' => BatteryItem::STATUS_AVAILABLE,
            'to_status' => BatteryItem::STATUS_INSPECTION,
            'note' => 'Régebbi változás.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $newerHistory = BatteryStatusHistory::query()->create([
            'battery_item_id' => $batteryItem->id,
            'changed_by_user_id' => null,
            'from_status' => BatteryItem::STATUS_INSPECTION,
            'to_status' => BatteryItem::STATUS_AVAILABLE,
            'note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}/status-history"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'status_history.0.id',
                $newerHistory->id
            )
            ->assertJsonPath(
                'status_history.1.id',
                $olderHistory->id
            );
    }

    public function test_history_can_be_empty(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $batteryItem = BatteryItem::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}/status-history"
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'battery_item' => [
                    'id' => $batteryItem->id,
                    'inventory_code' => $batteryItem->inventory_code,
                    'status' => $batteryItem->status,
                ],
                'status_history' => [],
            ]);
    }

    public function test_customer_cannot_view_battery_status_history(): void
    {
        $customer = User::factory()->create([
            'role' => 'CUSTOMER',
        ]);

        $batteryItem = BatteryItem::factory()->create();

        Sanctum::actingAs($customer);

        $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}/status-history"
        )->assertForbidden();
    }

    public function test_guest_cannot_view_battery_status_history(): void
    {
        $batteryItem = BatteryItem::factory()->create();

        $this->getJson(
            "/api/admin/battery-items/{$batteryItem->id}/status-history"
        )->assertUnauthorized();
    }

    public function test_missing_battery_item_returns_not_found(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson(
            '/api/admin/battery-items/999999/status-history'
        )->assertNotFound();
    }
}