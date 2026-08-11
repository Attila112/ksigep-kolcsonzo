import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminProductDetailResponse,
    AdminProductListResponse,
} from "@/types/adminProduct";

export async function getAdminProducts(): Promise<AdminProductListResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminProductListResponse>(
        "/admin/products",
        {
            token,
        }
    );
}

export async function getAdminProduct(
    productId: number
): Promise<AdminProductDetailResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminProductDetailResponse>(
        `/admin/products/${productId}`,
        {
            token,
        }
    );
}