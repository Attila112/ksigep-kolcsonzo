<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItemAllocation;
use App\Models\InventoryItem;
use App\Models\InventoryStatusHistory;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingIssueService
{
    /**
     * Assigns concrete physical machines to a confirmed booking.
     *
     * Every selected inventory item must be AVAILABLE and belong
     * to one of the products requested in the booking.
     * On success, allocations are created, machines become RENTED,
     * and the booking status changes to ACTIVE.
     *
     * Every inventory status change is also stored in the status history.
     *
     * @param array<int, int> $inventoryItemIds
     */
    public function issue(
        Booking $booking,
        array $inventoryItemIds,
    ): Booking {
        if ($booking->status !== 'CONFIRMED') {
            throw new DomainException(
                'Csak jóváhagyott foglalás adható ki.'
            );
        }

        return DB::transaction(function () use (
            $booking,
            $inventoryItemIds
        ): Booking {
            $booking->load('items.product');

            $requiredQuantity = $booking->items->sum('quantity');

            if (count($inventoryItemIds) !== $requiredQuantity) {
                throw new DomainException(
                    'A kiválasztott gépek száma nem egyezik a foglalásban szereplő mennyiséggel.'
                );
            }

            /** @var Collection<int, InventoryItem> $inventoryItems */
            $inventoryItems = InventoryItem::query()
                ->whereIn('id', $inventoryItemIds)
                ->lockForUpdate()
                ->get();

            if ($inventoryItems->count() !== count($inventoryItemIds)) {
                throw new DomainException(
                    'Az egyik kiválasztott gép nem található.'
                );
            }

            if ($inventoryItems->contains(
                fn (InventoryItem $item): bool =>
                    $item->status !== 'AVAILABLE'
            )) {
                throw new DomainException(
                    'Csak elérhető állapotú gép adható ki.'
                );
            }

            foreach ($booking->items as $bookingItem) {
                $matchingItems = $inventoryItems
                    ->where('product_id', $bookingItem->product_id)
                    ->values();

                if ($matchingItems->count() !== $bookingItem->quantity) {
                    throw new DomainException(
                        'A kiválasztott gépek egyike nem a foglalásban szereplő termékhez tartozik.'
                    );
                }

                foreach ($matchingItems as $inventoryItem) {
                    BookingItemAllocation::query()->create([
                        'booking_item_id' => $bookingItem->id,
                        'inventory_item_id' => $inventoryItem->id,
                        'assigned_at' => now(),
                        'returned_at' => null,
                    ]);

                    $previousStatus = $inventoryItem->status;

                    $inventoryItem->update([
                        'status' => 'RENTED',
                    ]);

                    InventoryStatusHistory::query()->create([
                        'inventory_item_id' => $inventoryItem->id,
                        'changed_by_user_id' => null,
                        'from_status' => $previousStatus,
                        'to_status' => 'RENTED',
                        'note' =>
                            'Automatikus státuszváltás gépkiadáskor.',
                    ]);
                }
            }

            $booking->update([
                'status' => 'ACTIVE',
            ]);

            return $booking->fresh([
                'items.product',
                'items.allocations.inventoryItem',
            ]);
        });
    }
}