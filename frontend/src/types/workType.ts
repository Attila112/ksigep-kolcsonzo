import type { Product } from "@/types/product";

export type WorkType = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon_key: string | null;
    sort_order: number;
};

export type WorkTypeListResponse = {
    work_types: WorkType[];
};
export type WorkTypeProductsResponse = {
    work_type: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        icon_key: string | null;
    };
    products: Product[];
};