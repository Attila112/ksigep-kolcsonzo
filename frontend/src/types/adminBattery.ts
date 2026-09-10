export type AdminBatteryItemType =
    | "BATTERY"
    | "CHARGER";

export type AdminBatteryStatus =
    | "AVAILABLE"
    | "RENTED"
    | "INSPECTION"
    | "MAINTENANCE"
    | "DAMAGED"
    | "INACTIVE";

export type AdminBatterySystem = {
    id: number;
    name: string;
    manufacturer: string;
    voltage: string;
};

export type AdminBatteryItem = {
    id: number;
    battery_system_id: number;

    inventory_code: string;
    type: AdminBatteryItemType;
    status: AdminBatteryStatus;

    serial_number: string | null;
    admin_note: string | null;

    battery_system: AdminBatterySystem;

    created_at: string;
    updated_at: string;
};

export type AdminBatteryListResponse = {
    battery_items: AdminBatteryItem[];
};
export type AdminBatteryDetail = AdminBatteryItem;

export type AdminBatteryDetailResponse = {
    battery_item: AdminBatteryDetail;
};
export type AdminBatteryStatusHistoryUser = {
    id: number;
    name: string;
};

export type AdminBatteryStatusHistory = {
    id: number;
    battery_item_id: number;
    changed_by_user_id: number | null;
    from_status: AdminBatteryStatus;
    to_status: AdminBatteryStatus;
    note: string | null;
    created_at: string;
    updated_at: string;
    changed_by: AdminBatteryStatusHistoryUser | null;
};

export type AdminBatteryStatusHistoryResponse = {
    battery_item: {
        id: number;
        inventory_code: string;
        status: AdminBatteryStatus;
    };
    status_history: AdminBatteryStatusHistory[];
};