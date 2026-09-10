"use server";

import { ApiError, apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminBatteryDetailResponse,
    AdminBatteryStatus,
} from "@/types/adminBattery";

type UpdateBatteryStatusResult =
    | {
          success: true;
          batteryItem: AdminBatteryDetailResponse["battery_item"];
      }
    | {
          success: false;
          message: string;
          errors?: Record<string, string[]>;
      };

export async function updateBatteryStatusAction(
    batteryItemId: number,
    status: AdminBatteryStatus,
    adminNote: string | null
): Promise<UpdateBatteryStatusResult> {
    const token = await getAuthToken();

    try {
        const response =
            await apiRequest<AdminBatteryDetailResponse>(
                `/admin/battery-items/${batteryItemId}/status`,
                {
                    method: "PATCH",
                    token,
                    body: JSON.stringify({
                        status,
                        admin_note: adminNote,
                    }),
                }
            );

        return {
            success: true,
            batteryItem: response.battery_item,
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