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