export type BookingPickupType =
    | "SELF_PICKUP"
    | "DELIVERY";

export type CreateBookingItem = {
    product_id: number;
    quantity: number;
};

export type CreateBookingRequest = {
    customer_name: string;
    customer_email: string;
    customer_phone: string;

    start_date: string;
    end_date: string;

    pickup_type: BookingPickupType;

    planned_pickup_at?: string | null;

    delivery_postal_code?: string | null;
    delivery_city?: string | null;
    delivery_street?: string | null;
    delivery_house_number?: string | null;

    customer_note?: string | null;

    items: CreateBookingItem[];
};

export type CreatedBookingProduct = {
    id: number;
    name: string;
};

export type CreatedBookingItem = {
    id: number;
    booking_id: number;
    product_id: number;
    inventory_item_id: number | null;
    quantity: number;
    price_per_day: number;
    deposit_per_item: number;
    rental_days: number;
    rental_subtotal: number;
    deposit_subtotal: number;
    product: CreatedBookingProduct;
};

export type CreatedBooking = {
    id: number;
    user_id: number | null;

    customer_name: string;
    customer_email: string;
    customer_phone: string;

    start_date: string;
    end_date: string;

    pickup_type: BookingPickupType;

    planned_pickup_at: string | null;

    delivery_postal_code: string | null;
    delivery_city: string | null;
    delivery_street: string | null;
    delivery_house_number: string | null;

    status: "PENDING";

    customer_note: string | null;
    admin_note: string | null;

    rental_total: number;
    deposit_total: number;
    total_payable: number;

    items: CreatedBookingItem[];
};

export type CreateBookingResponse = {
    message: string;
    booking: CreatedBooking;
};

export type BookingSuccessState = {
    bookingId: number;
    customerName: string;
    customerEmail: string;
    startDate: string;
    endDate: string;
    pickupType: BookingPickupType;
    rentalTotal: number;
    depositTotal: number;
    totalPayable: number;
};