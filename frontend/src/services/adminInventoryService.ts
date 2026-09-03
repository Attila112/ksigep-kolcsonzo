import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminInventoryDetailResponse,
    AdminInventoryListResponse,
    AdminInventoryStatusHistoryResponse,
} from "@/types/adminInventory";

export async function getAdminInventoryItems(): Promise<AdminInventoryListResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminInventoryListResponse>(
        "/admin/inventory-items",
        {
            token,
        }
    );
}

export async function getAdminInventoryItem(
    inventoryItemId: number
): Promise<AdminInventoryDetailResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminInventoryDetailResponse>(
        `/admin/inventory-items/${inventoryItemId}`,
        {
            token,
        }
    );
}

export async function getAdminInventoryStatusHistory(
    inventoryItemId: number
): Promise<AdminInventoryStatusHistoryResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminInventoryStatusHistoryResponse>(
        `/admin/inventory-items/${inventoryItemId}/status-history`,
        {
            token,
        }
    );
}