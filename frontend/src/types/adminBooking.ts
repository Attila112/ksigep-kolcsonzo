import type {
    AdminBatteryItem,
} from "@/types/adminBattery";

export type AdminBookingStatus =
    | "PENDING"
    | "CONFIRMED"
    | "REJECTED"
    | "CANCELLED"
    | "ACTIVE"
    | "COMPLETED";

export type AdminBookingPickupType =
    | "SELF_PICKUP"
    | "DELIVERY";

export type AdminBookingUser = {
    id: number;
    name: string;
    email: string;
};

export type AdminBookingProduct = {
    id: number;
    name: string;

    sku: string | null;

    price_per_day: string;
    deposit: string;

    battery_system_id: number | null;
    required_batteries: number;
    required_chargers: number;
};

export type AdminBookingInventoryItem = {
    id: number;
    product_id: number;

    inventory_code: string;
    serial_number: string | null;

    status:
        | "AVAILABLE"
        | "RENTED"
        | "INSPECTION"
        | "MAINTENANCE"
        | "DAMAGED"
        | "INACTIVE";

    admin_note: string | null;
};

export type AdminBookingBatteryItemAllocation = {
    id: number;

    booking_item_allocation_id: number;
    battery_item_id: number;

    assigned_at: string;
    returned_at: string | null;

    created_at: string;
    updated_at: string;

    battery_item: AdminBatteryItem;
};

export type AdminBookingItemAllocation = {
    id: number;

    booking_item_id: number;
    inventory_item_id: number;

    assigned_at: string;
    returned_at: string | null;

    created_at: string;
    updated_at: string;

    inventory_item: AdminBookingInventoryItem;

    battery_item_allocations: AdminBookingBatteryItemAllocation[];
};

export type AdminBookingItem = {
    id: number;

    booking_id: number;
    product_id: number;

    inventory_item_id: number | null;

    quantity: number;

    price_per_day: string;
    deposit_per_item: string;

    rental_days: number;

    rental_subtotal: string;
    deposit_subtotal: string;

    created_at: string;
    updated_at: string;

    product: AdminBookingProduct;

    allocations: AdminBookingItemAllocation[];
};

export type AdminBooking = {
    id: number;

    user_id: number | null;

    customer_name: string;
    customer_email: string;
    customer_phone: string;

    start_date: string;
    end_date: string;

    pickup_type: AdminBookingPickupType;

    planned_pickup_at: string | null;

    delivery_postal_code: string | null;
    delivery_city: string | null;
    delivery_street: string | null;
    delivery_house_number: string | null;

    delivery_latitude: string | null;
    delivery_longitude: string | null;
    delivery_distance_km: string | null;

    status: AdminBookingStatus;

    customer_note: string | null;
    admin_note: string | null;

    created_at: string;
    updated_at: string;

    rental_total: number;
    deposit_total: number;
    total_payable: number;

    user: AdminBookingUser | null;

    items: AdminBookingItem[];
};

export type AdminBookingDetailResponse = {
    booking: AdminBooking;
};

export type AdminBookingListResponse = {
    bookings: AdminBooking[];
};