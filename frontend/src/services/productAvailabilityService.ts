import { apiRequest } from "@/core/api/api";

import type {
    ProductAvailabilityCalendarResponse,
} from "@/types/productAvailability";

type GetProductAvailabilityCalendarParams = {
    productId: number;
    startDate: string;
    endDate: string;
};

type GetProductAvailabilityParams = {
    productId: number;
    startDate: string;
    endDate: string;
};

export type ProductAvailabilityResponse = {
    product_id: number;
    start_date: string;
    end_date: string;
    available_quantity: number;
    available: boolean;
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

export async function getProductAvailability({
    productId,
    startDate,
    endDate,
}: GetProductAvailabilityParams): Promise<ProductAvailabilityResponse> {
    const searchParams = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
    });

    return apiRequest<ProductAvailabilityResponse>(
        `/products/${productId}/availability?${searchParams.toString()}`
    );
}