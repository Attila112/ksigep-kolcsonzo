import { apiRequest } from "@/core/api/api";
import type {
    WorkTypeListResponse,
    WorkTypeProductsResponse,
} from "@/types/workType";

/**
 * Lekéri a publikus, aktív munkatípusokat
 * a backend által meghatározott sorrendben.
 */
export async function getWorkTypes(): Promise<WorkTypeListResponse> {
    return apiRequest<WorkTypeListResponse>("/work-types");
}
/**
 * Lekéri egy munkatípushoz tartozó publikus termékeket.
 */
export async function getWorkTypeProducts(
    slug: string
): Promise<WorkTypeProductsResponse> {
    return apiRequest<WorkTypeProductsResponse>(
        `/work-types/${slug}/products`
    );
}