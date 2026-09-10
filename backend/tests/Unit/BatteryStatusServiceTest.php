<?php

namespace Tests\Unit;

use App\Models\BatteryItem;
use App\Models\BatteryStatusHistory;
use App\Models\User;
use App\Services\BatteryStatusService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatteryStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_battery_item_status_and_creates_history(): void
    {
        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $admin = User::factory()->create();

        $service = app(
            BatteryStatusService::class
        );

        $updatedBatteryItem = $service->update(
            batteryItem: $batteryItem,
            status: BatteryItem::STATUS_MAINTENANCE,
            adminNote: 'Karbantartás szükséges.',
            changedBy: $admin,
        );

        $this->assertSame(
            BatteryItem::STATUS_MAINTENANCE,
            $updatedBatteryItem->status
        );

        $this->assertSame(
            'Karbantartás szükséges.',
            $updatedBatteryItem->admin_note
        );

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

    public function test_system_status_change_can_create_history_without_user(): void
    {
        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_AVAILABLE,
        ]);

        $service = app(
            BatteryStatusService::class
        );

        $service->update(
            batteryItem: $batteryItem,
            status: BatteryItem::STATUS_INSPECTION,
            adminNote: 'Automatikus státuszváltás.',
        );

        $history = BatteryStatusHistory::query()
            ->where(
                'battery_item_id',
                $batteryItem->id
            )
            ->firstOrFail();

        $this->assertNull(
            $history->changed_by_user_id
        );
    }

    public function test_rented_battery_item_cannot_be_manually_changed(): void
    {
        $batteryItem = BatteryItem::factory()->create([
            'status' => BatteryItem::STATUS_RENTED,
        ]);

        $service = app(
            BatteryStatusService::class
        );

        $this->expectException(
            DomainException::class
        );

        $service->update(
            batteryItem: $batteryItem,
            status: BatteryItem::STATUS_AVAILABLE,
            adminNote: null,
        );
    }
}