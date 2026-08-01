<?php

namespace App\Services;

use App\Models\BookingItem;
use App\Models\Product;
use Carbon\Carbon;
use InvalidArgumentException;

class BookingAvailabilityService
{
    /**
     * Returns the available inventory quantity for a product and date range.
     *
     * Active inventory items are counted, while overlapping PENDING,
     * CONFIRMED and ACTIVE bookings reduce the available quantity.
     * A booking can optionally be excluded, for example during approval.
     */
    public function availableQuantity(
        Product $product,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null,
    ): int {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException(
                'A befejezés dátuma nem lehet korábbi a kezdésnél.'
            );
        }

        $availableInventoryQuantity = $product
            ->inventoryItems()
            ->where('status', 'AVAILABLE')
            ->count();

        $reservedQuantity = BookingItem::query()
            ->where('product_id', $product->id)
            ->whereHas('booking', function ($query) use (
                $start,
                $end,
                $excludeBookingId
            ) {
                $query
                    ->whereIn('status', [
                        'PENDING',
                        'CONFIRMED',
                        'ACTIVE',
                    ])
                    ->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);

                if ($excludeBookingId !== null) {
                    $query->where('id', '!=', $excludeBookingId);
                }
            })
            ->sum('quantity');

        return max(
            0,
            $availableInventoryQuantity - $reservedQuantity
        );
    }
}
