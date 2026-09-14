<?php

namespace App\Services;

use App\Models\BookingItem;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

        $rentableInventoryQuantity = $product
            ->inventoryItems()
            ->whereIn('status', [
                'AVAILABLE',
                'RENTED',
            ])
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
            $rentableInventoryQuantity - $reservedQuantity
        );
    }

    /**
     * Returns daily availability for a product within a date range.
     *
     * The database is queried once for the rentable inventory quantity
     * and once for all bookings overlapping the requested calendar range.
     *
     * The returned array contains the available quantity and availability
     * state for every day in the requested period.
     *
     * @return array<int, array{
     *     date: string,
     *     available_quantity: int,
     *     available: bool
     * }>
     */
    public function availabilityCalendar(
        Product $product,
        string $startDate,
        string $endDate,
    ): array {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException(
                'A befejezés dátuma nem lehet korábbi a kezdésnél.'
            );
        }

        /*
         * A jelenleg AVAILABLE vagy RENTED állapotú gépek
         * számítanak a jövőbeli bérelhető kapacitásba.
         *
         * A RENTED azért számít bele, mert ha a jelenlegi
         * foglalása véget ér, egy későbbi időszakban ismét
         * bérelhető lehet.
         */
        $rentableInventoryQuantity = $product
            ->inventoryItems()
            ->whereIn('status', [
                'AVAILABLE',
                'RENTED',
            ])
            ->count();

        /*
         * Csak azokat a BookingItem rekordokat kérjük le,
         * amelyek bookingja legalább egy nappal átfedi
         * a teljes vizsgált naptári tartományt.
         *
         * Az egyes napokra vonatkozó foglaltságot ezután
         * PHP oldalon számoljuk ki.
         */
        $bookingItems = BookingItem::query()
            ->where('product_id', $product->id)
            ->whereHas('booking', function ($query) use (
                $start,
                $end
            ) {
                $query
                    ->whereIn('status', [
                        'PENDING',
                        'CONFIRMED',
                        'ACTIVE',
                    ])
                    ->whereDate(
                        'start_date',
                        '<=',
                        $end
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $start
                    );
            })
            ->with([
                'booking:id,start_date,end_date,status',
            ])
            ->get();

        $calendar = [];

        /*
         * Végigmegyünk a teljes dátumtartomány minden napján.
         */
        foreach (
            CarbonPeriod::create(
                $start,
                '1 day',
                $end
            ) as $date
        ) {
            /*
             * Összeadjuk azoknak a foglalásoknak a mennyiségét,
             * amelyek az adott napot lefedik.
             */
            $reservedQuantity = $bookingItems->sum(
                function (
                    BookingItem $bookingItem
                ) use ($date): int {
                    $booking =
                        $bookingItem->booking;

                    if ($booking === null) {
                        return 0;
                    }

                    $bookingStart =
                        $booking
                            ->start_date
                            ->copy()
                            ->startOfDay();

                    $bookingEnd =
                        $booking
                            ->end_date
                            ->copy()
                            ->startOfDay();

                    $overlapsDate =
                        $bookingStart->lte($date) &&
                        $bookingEnd->gte($date);

                    if (! $overlapsDate) {
                        return 0;
                    }

                    return (int) $bookingItem->quantity;
                }
            );

            $availableQuantity = max(
                0,
                $rentableInventoryQuantity -
                    $reservedQuantity
            );

            $calendar[] = [
                'date' =>
                    $date->format('Y-m-d'),

                'available_quantity' =>
                    $availableQuantity,

                'available' =>
                    $availableQuantity > 0,
            ];
        }

        return $calendar;
    }
}
