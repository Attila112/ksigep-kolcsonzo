import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminBatteryDetailResponse,
    AdminBatteryListResponse,
} from "@/types/adminBattery";

export async function getAdminBatteryItems(): Promise<AdminBatteryListResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminBatteryListResponse>(
        "/admin/battery-items",
        {
            token,
        }
    );
}
export async function getAdminBatteryItem(
    batteryItemId: number
): Promise<AdminBatteryDetailResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminBatteryDetailResponse>(
        `/admin/battery-items/${batteryItemId}`,
        {
            token,
        }
    );
}