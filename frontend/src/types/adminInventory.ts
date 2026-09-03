export type AdminInventoryStatus =
    | "AVAILABLE"
    | "RENTED"
    | "INSPECTION"
    | "MAINTENANCE"
    | "DAMAGED"
    | "INACTIVE";

export type AdminInventoryProduct = {
    id: number;
    name: string;
    sku: string | null;

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

    required_batteries: number;
    required_chargers: number;
};

export type AdminInventoryItem = {
    id: number;

    inventory_code: string;
    serial_number: string | null;

    status: AdminInventoryStatus;

    admin_note: string | null;

    product: AdminInventoryProduct;

    created_at: string;
    updated_at: string;
};

export type AdminInventoryListResponse = {
    inventory_items: AdminInventoryItem[];
};
export type AdminInventoryDetail = AdminInventoryItem;

export type AdminInventoryDetailResponse = {
    inventory_item: AdminInventoryDetail;
};

export type AdminInventoryStatusHistoryItem = {
    id: number;
    inventory_item_id: number;

    from_status: AdminInventoryStatus | null;
    to_status: AdminInventoryStatus;

    note: string | null;

    changed_by_user_id: number | null;

    changed_by: {
        id: number;
        name: string;
    } | null;

    created_at: string;
    updated_at: string;
};

export type AdminInventoryStatusHistoryResponse = {
    inventory_item: {
        id: number;
        inventory_code: string;
        status: AdminInventoryStatus;
    };

    status_history: AdminInventoryStatusHistoryItem[];
};