export type AdminCategoryLookupItem = {
    id: number;
    name: string;
    active: boolean;
};

export type AdminCategoryLookupResponse = {
    categories: AdminCategoryLookupItem[];
};

export type AdminBatterySystemLookupItem = {
    id: number;
    name: string;
    manufacturer: string;
    voltage: string;
    active: boolean;
};

export type AdminBatterySystemLookupResponse = {
    battery_systems: AdminBatterySystemLookupItem[];
};