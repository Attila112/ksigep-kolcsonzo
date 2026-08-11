import { apiRequest } from "@/core/api/api";
import { getAuthToken } from "@/core/auth/authCookie";

import type {
    AdminBatterySystemLookupResponse,
    AdminCategoryLookupResponse,
} from "@/types/adminLookup";

export async function getAdminCategories(): Promise<AdminCategoryLookupResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminCategoryLookupResponse>(
        "/admin/categories",
        {
            token,
        }
    );
}

export async function getAdminBatterySystems(): Promise<AdminBatterySystemLookupResponse> {
    const token = await getAuthToken();

    return apiRequest<AdminBatterySystemLookupResponse>(
        "/admin/battery-systems",
        {
            token,
        }
    );
}