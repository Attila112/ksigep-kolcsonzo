"use server";

import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type { AdminProductDetailResponse } from "@/types/adminProduct";

export type UpdateAdminProductData = {
    category_id: number;
    name: string;
    description: string;
    price_per_day: number;
    deposit: number;
    active: boolean;
    battery_system_id: number | null;
    required_batteries: number;
    required_chargers: number;
};

export async function updateAdminProduct(
    productId: number,
    data: UpdateAdminProductData
): Promise<AdminProductDetailResponse> {
    const token = await getAuthToken();

    if (!token) {
        throw new Error("UNAUTHENTICATED");
    }

    return apiRequest<AdminProductDetailResponse>(
        `/admin/products/${productId}`,
        {
            method: "PATCH",
            token,
            body: JSON.stringify(data),
        }
    );
}