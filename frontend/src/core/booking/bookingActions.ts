"use server";

import {
    ApiError,
    apiRequest,
} from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    CreateBookingRequest,
    CreateBookingResponse,
} from "@/types/booking";

export type CreateBookingActionResult =
    | {
          success: true;
          data: CreateBookingResponse;
      }
    | {
          success: false;
          message: string;
          errors?: Record<
              string,
              string[]
          >;
      };

export async function createBookingAction(
    data: CreateBookingRequest
): Promise<CreateBookingActionResult> {
    const token =
        await getAuthToken();

    try {
        const response =
            await apiRequest<CreateBookingResponse>(
                "/bookings",
                {
                    method: "POST",
                    body: JSON.stringify(
                        data
                    ),
                    token:
                        token ??
                        undefined,
                }
            );

        return {
            success: true,
            data: response,
        };
    } catch (error) {
        if (
            error instanceof
            ApiError
        ) {
            return {
                success: false,
                message:
                    error.message,
                errors:
                    error.errors,
            };
        }

        return {
            success: false,
            message:
                "UNKNOWN_ERROR",
        };
    }
}