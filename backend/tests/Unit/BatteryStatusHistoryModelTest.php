<?php

namespace Tests\Unit;

use App\Models\BatteryItem;
use App\Models\BatteryStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatteryStatusHistoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_battery_status_history_belongs_to_battery_item(): void
    {
        $batteryItem = BatteryItem::factory()->create();

        $history = BatteryStatusHistory::query()->create([
            'battery_item_id' => $batteryItem->id,
            'changed_by_user_id' => null,
            'from_status' => BatteryItem::STATUS_AVAILABLE,
            'to_status' => BatteryItem::STATUS_MAINTENANCE,
            'note' => 'Karbantartás szükséges.',
        ]);

        $this->assertTrue(
            $history->batteryItem->is($batteryItem)
        );
    }

    public function test_battery_status_history_belongs_to_changed_by_user(): void
    {
        $batteryItem = BatteryItem::factory()->create();
        $user = User::factory()->create();

        $history = BatteryStatusHistory::query()->create([
            'battery_item_id' => $batteryItem->id,
            'changed_by_user_id' => $user->id,
            'from_status' => BatteryItem::STATUS_AVAILABLE,
            'to_status' => BatteryItem::STATUS_INACTIVE,
            'note' => 'Ideiglenesen kivonva.',
        ]);

        $this->assertTrue(
            $history->changedBy->is($user)
        );
    }
}