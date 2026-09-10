import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminBatteryDetailResponse,
    AdminBatteryListResponse,
    AdminBatteryStatusHistoryResponse,
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
export async function getAdminBatteryStatusHistory(
    batteryItemId: number
): Promise<AdminBatteryStatusHistoryResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminBatteryStatusHistoryResponse>(
        `/admin/battery-items/${batteryItemId}/status-history`,
        {
            token,
        }
    );
}