export type BookingCartItem = {
    productId: number;
    productName: string;
    pricePerDay: number;
    deposit: number;
    quantity: number;
};

export type BookingCartPeriod = {
    startDate: string;
    endDate: string;
};

export type BookingCartState = {
    period: BookingCartPeriod | null;
    items: BookingCartItem[];
};