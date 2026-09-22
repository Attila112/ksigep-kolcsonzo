import type {
    BookingCartItem,
    BookingCartPeriod,
} from "@/types/bookingCart";

const MILLISECONDS_PER_DAY =
    24 * 60 * 60 * 1000;

function parseCalendarDate(
    value: string
): Date {
    const [year, month, day] = value
        .split("-")
        .map(Number);

    return new Date(
        Date.UTC(year, month - 1, day)
    );
}

export function calculateRentalDays(
    period: BookingCartPeriod
): number {
    const startDate = parseCalendarDate(
        period.startDate
    );

    const endDate = parseCalendarDate(
        period.endDate
    );

    const difference =
        endDate.getTime() -
        startDate.getTime();

    return (
        Math.floor(
            difference /
                MILLISECONDS_PER_DAY
        ) + 1
    );
}

export function calculateItemRentalTotal(
    item: BookingCartItem,
    rentalDays: number
): number {
    return (
        item.pricePerDay *
        item.quantity *
        rentalDays
    );
}

export function calculateItemDepositTotal(
    item: BookingCartItem
): number {
    return item.deposit * item.quantity;
}