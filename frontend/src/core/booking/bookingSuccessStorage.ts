import type {
    BookingSuccessState,
    CreatedBooking,
} from "@/types/booking";

const STORAGE_KEY =
    "booking-success";

export function saveBookingSuccess(
    booking: CreatedBooking
): void {
    if (
        typeof window ===
        "undefined"
    ) {
        return;
    }

    const state: BookingSuccessState =
    {
        bookingId: booking.id,
        customerName:
            booking.customer_name,
        customerEmail:
            booking.customer_email,
        startDate:
            booking.start_date,
        endDate:
            booking.end_date,
        pickupType:
            booking.pickup_type,
        rentalTotal:
            Number(
                booking.rental_total
            ),
        depositTotal:
            Number(
                booking.deposit_total
            ),
        totalPayable:
            Number(
                booking.total_payable
            ),
    };

    window.sessionStorage.setItem(
        STORAGE_KEY,
        JSON.stringify(state)
    );
}

export function loadBookingSuccess():
    BookingSuccessState | null {
    if (
        typeof window ===
        "undefined"
    ) {
        return null;
    }

    const stored =
        window.sessionStorage.getItem(
            STORAGE_KEY
        );

    if (!stored) {
        return null;
    }

    try {
        return JSON.parse(
            stored
        ) as BookingSuccessState;
    } catch {
        window.sessionStorage.removeItem(
            STORAGE_KEY
        );

        return null;
    }
}

export function clearBookingSuccess(): void {
    if (
        typeof window ===
        "undefined"
    ) {
        return;
    }

    window.sessionStorage.removeItem(
        STORAGE_KEY
    );
}