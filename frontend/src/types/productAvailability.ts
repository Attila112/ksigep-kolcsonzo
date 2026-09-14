export type ProductAvailabilityDay = {
    date: string;
    available_quantity: number;
    available: boolean;
};

export type ProductAvailabilityCalendarResponse = {
    product_id: number;
    start_date: string;
    end_date: string;
    days: ProductAvailabilityDay[];
};