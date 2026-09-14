import { apiRequest } from "@/core/api/api";
import type { ProductAvailabilityCalendarResponse } from "@/types/productAvailability";

type GetProductAvailabilityCalendarParams = {
    productId: number;
    startDate: string;
    endDate: string;
};

export async function getProductAvailabilityCalendar({
    productId,
    startDate,
    endDate,
}: GetProductAvailabilityCalendarParams): Promise<ProductAvailabilityCalendarResponse> {
    const searchParams = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
    });

    return apiRequest<ProductAvailabilityCalendarResponse>(
        `/products/${productId}/availability-calendar?${searchParams.toString()}`
    );
}