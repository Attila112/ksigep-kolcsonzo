import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";
import type { AdminProductListResponse } from "@/types/adminProduct";

export async function getAdminProducts(): Promise<AdminProductListResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminProductListResponse>(
        "/admin/products",
        {
            token,
        }
    );
}