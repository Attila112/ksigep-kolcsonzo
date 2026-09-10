<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItemAllocation;
use App\Models\InventoryStatusHistory;
use DomainException;
use Illuminate\Support\Facades\DB;
use App\Models\BatteryItem;
use App\Models\BatteryStatusHistory;
use App\Models\BookingItemAllocationBatteryItem;

class BookingReturnService
{
    /**
     * Returns selected physical machines from an active booking.
     *
     * Returned machines move to INSPECTION instead of AVAILABLE,
     * because an administrator must inspect them before deciding
     * whether they can be rented again.
     *
     * The booking remains ACTIVE while at least one allocated machine
     * has not been returned. It becomes COMPLETED only after every
     * allocation has a returned_at timestamp.
     *
     * Every inventory status change is also stored in the status history.
     *
     * @param array<int, int> $inventoryItemIds
     */
    public function returnItems(
        Booking $booking,
        array $inventoryItemIds,
    ): Booking {
        if ($booking->status !== 'ACTIVE') {
            throw new DomainException(
                'Csak aktív foglaláshoz vehető vissza gép.'
            );
        }

        return DB::transaction(function () use (
            $booking,
            $inventoryItemIds
        ): Booking {
            $allocations = BookingItemAllocation::query()
                ->whereIn('inventory_item_id', $inventoryItemIds)
                ->whereHas(
                    'bookingItem',
                    fn($query) => $query->where(
                        'booking_id',
                        $booking->id
                    )
                )
                ->with('inventoryItem')
                ->lockForUpdate()
                ->get();

            /*
             * Ha kevesebb allocationt találtunk, mint amennyi gépet
             * elküldtek, akkor valamelyik gép másik foglaláshoz tartozik.
             */
            if ($allocations->count() !== count($inventoryItemIds)) {
                throw new DomainException(
                    'A kiválasztott gépek egyike nem tartozik ehhez a foglaláshoz.'
                );
            }

            if ($allocations->contains(
                fn(BookingItemAllocation $allocation): bool =>
                $allocation->returned_at !== null
            )) {
                throw new DomainException(
                    'A kiválasztott gépek egyike már visszavételre került.'
                );
            }

            foreach ($allocations as $allocation) {
                $allocation->update([
                    'returned_at' => now(),
                ]);

                $inventoryItem = $allocation->inventoryItem;

                $previousStatus = $inventoryItem->status;

                $inventoryItem->update([
                    'status' => 'INSPECTION',
                ]);

                InventoryStatusHistory::query()->create([
                    'inventory_item_id' => $inventoryItem->id,
                    'changed_by_user_id' => null,
                    'from_status' => $previousStatus,
                    'to_status' => 'INSPECTION',
                    'note' =>
                    'Automatikus státuszváltás gépvisszavételkor.',
                ]);

                $batteryAllocations =
                    BookingItemAllocationBatteryItem::query()
                    ->where(
                        'booking_item_allocation_id',
                        $allocation->id
                    )
                    ->whereNull('returned_at')
                    ->with('batteryItem')
                    ->lockForUpdate()
                    ->get();

                foreach ($batteryAllocations as $batteryAllocation) {
                    $batteryItem = $batteryAllocation->batteryItem;

                    $batteryAllocation->update([
                        'returned_at' => now(),
                    ]);

                    $previousBatteryStatus = $batteryItem->status;

                    $batteryItem->update([
                        'status' => BatteryItem::STATUS_INSPECTION,
                    ]);

                    BatteryStatusHistory::query()->create([
                        'battery_item_id' => $batteryItem->id,
                        'changed_by_user_id' => null,
                        'from_status' => $previousBatteryStatus,
                        'to_status' => BatteryItem::STATUS_INSPECTION,
                        'note' =>
                        'Automatikus státuszváltás akkumulátor vagy töltő visszavételekor.',
                    ]);
                }
            }

            /*
             * Megnézzük, maradt-e még olyan gép ennél a foglalásnál,
             * amelyet nem hoztak vissza.
             */
            $hasUnreturnedAllocations = BookingItemAllocation::query()
                ->whereHas(
                    'bookingItem',
                    fn($query) => $query->where(
                        'booking_id',
                        $booking->id
                    )
                )
                ->whereNull('returned_at')
                ->exists();

            if (! $hasUnreturnedAllocations) {
                $booking->update([
                    'status' => 'COMPLETED',
                ]);
            }

            return $booking->fresh([
                'items.product',
                'items.allocations.inventoryItem',
                'items.allocations.batteryItemAllocations.batteryItem.batterySystem',
            ]);
        });
    }
}
