"use server";

import { ApiError, apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminInventoryDetail,
    AdminInventoryStatus,
} from "@/types/adminInventory";

export type UpdateAdminInventoryStatusData = {
    status: AdminInventoryStatus;
    admin_note: string | null;
};

type UpdateAdminInventoryStatusResponse = {
    message: string;
    inventory_item: AdminInventoryDetail;
};

export type UpdateAdminInventoryStatusResult =
    | {
        success: true;
        inventoryItem: AdminInventoryDetail;
    }
    | {
        success: false;
        message: string | null;
        errors?: Record<string, string[]>;
    };

export async function updateAdminInventoryStatus(
    inventoryItemId: number,
    data: UpdateAdminInventoryStatusData
): Promise<UpdateAdminInventoryStatusResult> {
    const token = await getAuthToken();

    if (!token) {
        return {
            success: false,
            message: null,
        };
    }

    try {
        const response =
            await apiRequest<UpdateAdminInventoryStatusResponse>(
                `/admin/inventory-items/${inventoryItemId}/status`,
                {
                    method: "PATCH",
                    token,
                    body: JSON.stringify(data),
                }
            );

        return {
            success: true,
            inventoryItem: response.inventory_item,
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
            message: null,
        };
    }
}