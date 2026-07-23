<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class BookingPriceCalculator
{
    public function calculate(
        string $startDate,
        string $endDate,
        string|int|float $pricePerDay,
        string|int|float $depositPerItem,
        int $quantity = 1,
    ): array {
        if ($quantity < 1) {
            throw new InvalidArgumentException(
                'A mennyiségnek legalább 1-nek kell lennie.'
            );
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException(
                'A befejezés dátuma nem lehet korábbi a kezdésnél.'
            );
        }

        // A kezdő- és a zárónap is kölcsönzési napnak számít.
        $rentalDays = $start->diffInDays($end) + 1;

        $pricePerDay = (float) $pricePerDay;
        $depositPerItem = (float) $depositPerItem;

        return [
            'quantity' => $quantity,
            'price_per_day' => $pricePerDay,
            'deposit_per_item' => $depositPerItem,
            'rental_days' => $rentalDays,
            'rental_subtotal' => $rentalDays * $pricePerDay * $quantity,
            'deposit_subtotal' => $depositPerItem * $quantity,
        ];
    }
}