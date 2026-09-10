<?php

namespace App\Services;

use App\Models\BatteryItem;
use App\Models\BatteryStatusHistory;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class BatteryStatusService
{
    public function update(
        BatteryItem $batteryItem,
        string $status,
        ?string $adminNote = null,
        ?User $changedBy = null,
    ): BatteryItem {
        if (
            $batteryItem->status ===
            BatteryItem::STATUS_RENTED
        ) {
            throw new DomainException(
                'Kiadott akkumulátor vagy töltő állapota csak a visszavételi folyamatban módosítható.'
            );
        }

        return DB::transaction(
            function () use (
                $batteryItem,
                $status,
                $adminNote,
                $changedBy
            ): BatteryItem {
                $previousStatus =
                    $batteryItem->status;

                $batteryItem->update([
                    'status' => $status,
                    'admin_note' => $adminNote,
                ]);

                BatteryStatusHistory::query()
                    ->create([
                        'battery_item_id' =>
                            $batteryItem->id,

                        'changed_by_user_id' =>
                            $changedBy?->id,

                        'from_status' =>
                            $previousStatus,

                        'to_status' =>
                            $status,

                        'note' =>
                            $adminNote,
                    ]);

                return $batteryItem->fresh([
                    'batterySystem:id,name,manufacturer,voltage',
                ]);
            }
        );
    }
}