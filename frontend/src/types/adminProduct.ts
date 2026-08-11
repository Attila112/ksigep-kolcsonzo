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