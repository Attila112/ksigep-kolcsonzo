<?php

namespace App\Services;

use App\Models\InventoryItem;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoryStatusService
{
    /**
     * Changes the operational status of a physical machine.
     *
     * RENTED machines cannot be changed manually because their state
     * is controlled by the booking issue and return workflows.
     */
    public function update(
        InventoryItem $inventoryItem,
        string $status,
        ?string $adminNote = null,
    ): InventoryItem {
        if ($inventoryItem->status === 'RENTED') {
            throw new DomainException(
                'Kiadott gép állapota csak a visszavételi folyamatban módosítható.'
            );
        }

        return DB::transaction(function () use (
            $inventoryItem,
            $status,
            $adminNote
        ): InventoryItem {
            $inventoryItem->update([
                'status' => $status,
                'admin_note' => $adminNote,
            ]);

            return $inventoryItem->fresh([
                'product:id,name',
            ]);
        });
    }
}