<?php

namespace Tests\Unit;

use App\Services\BookingPriceCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class BookingPriceCalculatorTest extends TestCase
{
    public function test_it_calculates_booking_price_correctly(): void
    {
        $calculator = new BookingPriceCalculator();

        $result = $calculator->calculate(
            startDate: '2026-08-10',
            endDate: '2026-08-12',
            pricePerDay: 8000,
            depositPerItem: 30000,
            quantity: 2,
        );

        $this->assertEquals(2, $result['quantity']);
        $this->assertEquals(8000, $result['price_per_day']);
        $this->assertEquals(30000, $result['deposit_per_item']);
        $this->assertEquals(3, $result['rental_days']);
        $this->assertEquals(48000, $result['rental_subtotal']);
        $this->assertEquals(60000, $result['deposit_subtotal']);
    }

    public function test_it_throws_exception_when_end_date_is_before_start_date(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $calculator = new BookingPriceCalculator();

        $calculator->calculate(
            startDate: '2026-08-15',
            endDate: '2026-08-10',
            pricePerDay: 8000,
            depositPerItem: 30000,
        );
    }

    public function test_it_throws_exception_when_quantity_is_less_than_one(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $calculator = new BookingPriceCalculator();

        $calculator->calculate(
            startDate: '2026-08-10',
            endDate: '2026-08-12',
            pricePerDay: 8000,
            depositPerItem: 30000,
            quantity: 0,
        );
    }
}