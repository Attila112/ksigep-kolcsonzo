<?php

namespace App\Services;

use App\Models\BookingItem;
use App\Models\Product;
use Carbon\Carbon;
use InvalidArgumentException;

class BookingAvailabilityService
{
    public function availableQuantity(
        Product $product,
        string $startDate,
        string $endDate,
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
            ->whereHas('booking', function ($query) use ($start, $end) {
                $query
                    ->whereIn('status', [
                        'PENDING',
                        'CONFIRMED',
                        'ACTIVE',
                    ])
                    ->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start);
            })
            ->sum('quantity');

        return max(
            0,
            $availableInventoryQuantity - $reservedQuantity
        );
    }
}
