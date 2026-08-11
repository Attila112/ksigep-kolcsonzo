export type AdminProduct = {
    id: number;
    name: string;
    sku: string | null;
    image_path: string | null;
    price_per_day: string;
    deposit: string;
    active: boolean;

    inventory_items_count: number;
    available_inventory_count: number;

    category: {
        id: number;
        name: string;
    };

    battery_system: {
        id: number;
        name: string;
        manufacturer: string;
        voltage: string;
    } | null;
};

export type AdminProductListResponse = {
    products: AdminProduct[];
};
export type AdminInventoryItem = {
    id: number;
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

export type AdminWorkType = {
    id: number;
    name: string;
    slug: string;
};

export type AdminProductDetail = AdminProduct & {
    description: string;
    deposit: string;

    required_batteries: number;
    required_chargers: number;

    rented_inventory_count: number;
    maintenance_inventory_count: number;

    inventory_items: AdminInventoryItem[];
    work_types: AdminWorkType[];
};

export type AdminProductDetailResponse = {
    product: AdminProductDetail;
};