<?php

namespace App\Services;

use App\Models\BatteryItem;
use App\Models\BatteryStatusHistory;
use App\Models\Booking;
use App\Models\BookingItemAllocation;
use App\Models\BookingItemAllocationBatteryItem;
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
     * @param array<int, int> $inventoryItemIds
     * @param array<int, array{
     *     inventory_item_id: int,
     *     battery_item_ids: array<int, int>
     * }> $batteryAllocations
     */
    public function issue(
        Booking $booking,
        array $inventoryItemIds,
        array $batteryAllocations = [],
    ): Booking {
        if ($booking->status !== 'CONFIRMED') {
            throw new DomainException(
                'Csak jóváhagyott foglalás adható ki.'
            );
        }

        return DB::transaction(function () use (
            $booking,
            $inventoryItemIds,
            $batteryAllocations
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
                fn(InventoryItem $item): bool =>
                $item->status !== 'AVAILABLE'
            )) {
                throw new DomainException(
                    'Csak elérhető állapotú gép adható ki.'
                );
            }
            $selectedInventoryItemIds = $inventoryItems
                ->pluck('id')
                ->map(fn($id): int => (int) $id)
                ->all();

            $batteryAllocationInventoryItemIds = collect($batteryAllocations)
                ->pluck('inventory_item_id')
                ->map(fn($id): int => (int) $id)
                ->all();

            foreach ($batteryAllocationInventoryItemIds as $inventoryItemId) {
                if (!in_array(
                    $inventoryItemId,
                    $selectedInventoryItemIds,
                    true
                )) {
                    throw new DomainException(
                        'Akkumulátor vagy töltő csak a kiadáshoz kiválasztott géphez rendelhető.'
                    );
                }
            }

            $allBatteryItemIds = collect($batteryAllocations)
                ->flatMap(
                    fn(array $allocation): array =>
                    $allocation['battery_item_ids']
                )
                ->map(fn($id): int => (int) $id)
                ->all();

            if (
                count($allBatteryItemIds) !==
                count(array_unique($allBatteryItemIds))
            ) {
                throw new DomainException(
                    'Ugyanaz az akkumulátor vagy töltő nem rendelhető egyszerre több géphez.'
                );
            }

            $batteryAllocationsByInventoryItem = collect(
                $batteryAllocations
            )->keyBy('inventory_item_id');
            /*
             * Előkészítjük a kliens által küldött akku/töltő
             * hozzárendeléseket inventory item ID szerint.
             */
            $batteryAllocationsByInventoryItem = collect(
                $batteryAllocations
            )->keyBy('inventory_item_id');

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
                    $machineAllocation =
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

                    $batteryAllocationData =
                        $batteryAllocationsByInventoryItem->get(
                            $inventoryItem->id
                        );

                    $requiresBatteryItems =
                        (int) $bookingItem->product->required_batteries > 0
                        || (int) $bookingItem->product->required_chargers > 0;

                    if ($batteryAllocationData === null) {
                        if ($requiresBatteryItems) {
                            throw new DomainException(
                                'Az akkumulátoros géphez ki kell választani a szükséges akkumulátorokat és töltőket.'
                            );
                        }

                        continue;
                    }

                    if (!$requiresBatteryItems) {
                        throw new DomainException(
                            'Ehhez a géphez nem rendelhető akkumulátor vagy töltő.'
                        );
                    }

                    $batteryItemIds =
                        $batteryAllocationData['battery_item_ids'];

                    /** @var Collection<int, BatteryItem> $batteryItems */
                    $batteryItems = BatteryItem::query()
                        ->whereIn('id', $batteryItemIds)
                        ->lockForUpdate()
                        ->get();

                    if (
                        $batteryItems->count() !==
                        count($batteryItemIds)
                    ) {
                        throw new DomainException(
                            'Az egyik kiválasztott akkumulátor vagy töltő nem található.'
                        );
                    }
                    if ($batteryItems->contains(
                        fn(BatteryItem $batteryItem): bool =>
                        $batteryItem->status !== BatteryItem::STATUS_AVAILABLE
                    )) {
                        throw new DomainException(
                            'Csak elérhető állapotú akkumulátor vagy töltő adható ki.'
                        );
                    }
                    if ($batteryItems->contains(
                        fn(BatteryItem $batteryItem): bool =>
                        $batteryItem->battery_system_id !==
                            $bookingItem->product->battery_system_id
                    )) {
                        throw new DomainException(
                            'A kiválasztott akkumulátor vagy töltő nem kompatibilis a gép akkumulátorrendszerével.'
                        );
                    }
                    $selectedBatteryCount = $batteryItems
                        ->where('type', BatteryItem::TYPE_BATTERY)
                        ->count();

                    if (
                        $selectedBatteryCount !==
                        (int) $bookingItem->product->required_batteries
                    ) {
                        throw new DomainException(
                            'A kiválasztott akkumulátorok száma nem egyezik a géphez szükséges mennyiséggel.'
                        );
                    }
                    $selectedChargerCount = $batteryItems
                        ->where('type', BatteryItem::TYPE_CHARGER)
                        ->count();

                    if (
                        $selectedChargerCount !==
                        (int) $bookingItem->product->required_chargers
                    ) {
                        throw new DomainException(
                            'A kiválasztott töltők száma nem egyezik a géphez szükséges mennyiséggel.'
                        );
                    }

                    foreach ($batteryItems as $batteryItem) {
                        BookingItemAllocationBatteryItem::query()
                            ->create([
                                'booking_item_allocation_id' =>
                                $machineAllocation->id,
                                'battery_item_id' =>
                                $batteryItem->id,
                                'assigned_at' => now(),
                                'returned_at' => null,
                            ]);

                        $previousBatteryStatus =
                            $batteryItem->status;

                        $batteryItem->update([
                            'status' =>
                            BatteryItem::STATUS_RENTED,
                        ]);

                        BatteryStatusHistory::query()->create([
                            'battery_item_id' =>
                            $batteryItem->id,
                            'changed_by_user_id' => null,
                            'from_status' =>
                            $previousBatteryStatus,
                            'to_status' =>
                            BatteryItem::STATUS_RENTED,
                            'note' =>
                            'Automatikus státuszváltás akkumulátor vagy töltő kiadásakor.',
                        ]);
                    }
                }
            }

            $booking->update([
                'status' => 'ACTIVE',
            ]);

            return $booking->fresh([
                'items.product',
                'items.allocations.inventoryItem',
                'items.allocations.batteryItemAllocations.batteryItem.batterySystem',
            ]);
        });
    }
}
