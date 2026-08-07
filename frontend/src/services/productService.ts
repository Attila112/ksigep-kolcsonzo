import type {  ProductDetailResponse, ProductListResponse } from "@/types/product";
import { apiRequest } from "@/core/api/api";

export async function getProducts(): Promise<ProductListResponse> {
    return apiRequest<ProductListResponse>("/products");
}

export async function getProduct(
    productId: number
): Promise<ProductDetailResponse> {
    return apiRequest<ProductDetailResponse>(
        `/products/${productId}`
    );
}