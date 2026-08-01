<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryStatusHistory;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoryStatusService
{
    /**
     * Changes the operational status of a physical machine.
     *
     * RENTED machines cannot be changed manually because their status
     * is controlled by the booking issue and return workflows.
     *
     * Every successful status change is recorded in the machine's
     * status history together with the acting administrator.
     */
    public function update(
        InventoryItem $inventoryItem,
        string $status,
        ?string $adminNote = null,
        ?User $changedBy = null,
    ): InventoryItem {
        if ($inventoryItem->status === 'RENTED') {
            throw new DomainException(
                'Kiadott gép állapota csak a visszavételi folyamatban módosítható.'
            );
        }

        return DB::transaction(function () use (
            $inventoryItem,
            $status,
            $adminNote,
            $changedBy
        ): InventoryItem {
            $previousStatus = $inventoryItem->status;

            $inventoryItem->update([
                'status' => $status,
                'admin_note' => $adminNote,
            ]);

            InventoryStatusHistory::query()->create([
                'inventory_item_id' => $inventoryItem->id,
                'changed_by_user_id' => $changedBy?->id,
                'from_status' => $previousStatus,
                'to_status' => $status,
                'note' => $adminNote,
            ]);

            return $inventoryItem->fresh([
                'product:id,name',
            ]);
        });
    }
}
