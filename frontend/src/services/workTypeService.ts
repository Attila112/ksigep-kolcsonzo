import { apiRequest } from "@/core/api/api";
import type { WorkTypeListResponse } from "@/types/workType";

/**
 * Lekéri a publikus, aktív munkatípusokat
 * a backend által meghatározott sorrendben.
 */
export async function getWorkTypes(): Promise<WorkTypeListResponse> {
    return apiRequest<WorkTypeListResponse>("/work-types");
}