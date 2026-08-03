import { apiRequest } from "@/core/api/api";
import type { ProductListResponse } from "@/types/product";

/**
 * Lekéri az összes publikus, aktív terméket a backend API-ból.
 */
export async function getProducts(): Promise<ProductListResponse> {
    return apiRequest<ProductListResponse>("/products");
}