"use server";

import { ApiError, apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminBooking,
} from "@/types/adminBooking";

type BookingActionResult =
    | {
        success: true;
        booking: AdminBooking;
    }
    | {
        success: false;
        message: string;
        errors?: Record<string, string[]>;
    };

type BookingActionResponse = {
    message: string;
    booking: AdminBooking;
};

/**
 * Jóváhagy egy PENDING állapotú foglalást.
 */
export async function approveBookingAction(
    bookingId: number
): Promise<BookingActionResult> {
    const token = await getAuthToken();

    try {
        const response =
            await apiRequest<BookingActionResponse>(
                `/admin/bookings/${bookingId}/approve`,
                {
                    method: "POST",
                    token,
                }
            );

        return {
            success: true,
            booking: response.booking,
        };
    } catch (error) {
        if (error instanceof ApiError) {
            return {
                success: false,
                message: error.message,
                errors: error.errors,
            };
        }

        return {
            success: false,
            message: "UNKNOWN_ERROR",
        };
    }
}

/**
 * Elutasít egy PENDING állapotú foglalást.
 */
export async function rejectBookingAction(
    bookingId: number,
    reason: string
): Promise<BookingActionResult> {
    const token = await getAuthToken();

    try {
        const response =
            await apiRequest<BookingActionResponse>(
                `/admin/bookings/${bookingId}/reject`,
                {
                    method: "POST",
                    token,
                    body: JSON.stringify({
                        reason,
                    }),
                }
            );

        return {
            success: true,
            booking: response.booking,
        };
    } catch (error) {
        if (error instanceof ApiError) {
            return {
                success: false,
                message: error.message,
                errors: error.errors,
            };
        }

        return {
            success: false,
            message: "UNKNOWN_ERROR",
        };
    }
}
export type IssueBookingBatteryAllocation = {
    inventory_item_id: number;
    battery_item_ids: number[];
};

type IssueBookingActionResult =
    | {
        success: true;
        booking: AdminBooking;
    }
    | {
        success: false;
        message: string;
        errors?: Record<string, string[]>;
    };

export async function issueBookingAction(
    bookingId: number,
    inventoryItemIds: number[],
    batteryAllocations: IssueBookingBatteryAllocation[]
): Promise<IssueBookingActionResult> {
    const token = await getAuthToken();

    try {
        const response =
            await apiRequest<BookingActionResponse>(
                `/admin/bookings/${bookingId}/issue`,
                {
                    method: "POST",
                    token,
                    body: JSON.stringify({
                        inventory_item_ids:
                            inventoryItemIds,
                        battery_allocations:
                            batteryAllocations,
                    }),
                }
            );

        return {
            success: true,
            booking: response.booking,
        };
    } catch (error) {
        if (error instanceof ApiError) {
            return {
                success: false,
                message: error.message,
                errors: error.errors,
            };
        }

        return {
            success: false,
            message: "UNKNOWN_ERROR",
        };
    }
}