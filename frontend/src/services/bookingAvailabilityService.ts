import { apiRequest } from "@/core/api/api";

import type {
    BookingAvailabilityRequest,
    BookingAvailabilityResponse,
} from "@/types/bookingAvailability";

export async function checkBookingAvailability(
    data: BookingAvailabilityRequest
): Promise<BookingAvailabilityResponse> {
    return apiRequest<BookingAvailabilityResponse>(
        "/booking/availability",
        {
            method: "POST",
            body: JSON.stringify(data),
        }
    );
}