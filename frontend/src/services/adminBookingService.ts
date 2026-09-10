import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminBookingDetailResponse,
    AdminBookingListResponse,
} from "@/types/adminBooking";

export async function getAdminBookings(): Promise<AdminBookingListResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminBookingListResponse>(
        "/admin/bookings",
        {
            token,
        }
    );
}

export async function getAdminBooking(
    bookingId: number
): Promise<AdminBookingDetailResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminBookingDetailResponse>(
        `/admin/bookings/${bookingId}`,
        {
            token,
        }
    );
}