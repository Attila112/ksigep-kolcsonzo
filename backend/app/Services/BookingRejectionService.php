<?php

namespace App\Services;

use App\Models\Booking;
use DomainException;
use Illuminate\Support\Facades\DB;

class BookingRejectionService
{
    /**
     * Rejects a pending booking and stores the reason for the customer.
     *
     * Only bookings in PENDING status can be rejected.
     */
    public function reject(Booking $booking, string $reason): Booking
    {
        if ($booking->status !== 'PENDING') {
            throw new DomainException(
                'Csak függőben lévő foglalás utasítható el.'
            );
        }

        return DB::transaction(function () use (
            $booking,
            $reason
        ): Booking {
            $booking->update([
                'status' => 'REJECTED',
                'admin_note' => $reason,
            ]);

            return $booking->fresh([
                'items.product',
            ]);
        });
    }
}
