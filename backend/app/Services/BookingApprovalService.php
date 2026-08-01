<?php

namespace App\Services;

use App\Models\Booking;
use DomainException;
use Illuminate\Support\Facades\DB;

class BookingApprovalService
{
    public function __construct(
        private readonly BookingAvailabilityService $availabilityService,
    ) {}

    /**
     * Approves a pending booking after rechecking availability.
     *
     * Concrete inventory items are not assigned here. Their selection
     * happens only when the machines are physically handed over.
     */
    public function approve(Booking $booking): Booking
    {
        if ($booking->status !== 'PENDING') {
            throw new DomainException(
                'Csak függőben lévő foglalás hagyható jóvá.'
            );
        }

        return DB::transaction(function () use ($booking): Booking {
            $booking->load('items.product');

            foreach ($booking->items as $item) {
                $availableQuantity = $this->availabilityService
                    ->availableQuantity(
                        product: $item->product,
                        startDate: $booking->start_date->toDateString(),
                        endDate: $booking->end_date->toDateString(),
                        excludeBookingId: $booking->id,
                    );

                if ($item->quantity > $availableQuantity) {
                    throw new DomainException(sprintf(
                        'A(z) %s termékből nincs elegendő szabad darab a kiválasztott időszakban.',
                        $item->product->name,
                    ));
                }
            }

            $booking->update([
                'status' => 'CONFIRMED',
            ]);

            return $booking->fresh([
                'items.product',
            ]);
        });
    }
}
