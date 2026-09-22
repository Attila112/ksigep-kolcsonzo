export type BookingAvailabilityRequestItem = {
    product_id: number;
};

export type BookingAvailabilityRequest = {
    start_date: string;
    end_date: string;
    items: BookingAvailabilityRequestItem[];
};

export type BookingAvailabilityItem = {
    product_id: number;
    available_quantity: number;
    available: boolean;
};

export type BookingAvailabilityResponse = {
    start_date: string;
    end_date: string;
    items: BookingAvailabilityItem[];
};