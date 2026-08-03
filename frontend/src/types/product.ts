export type ProductCategory = {
    id: number;
    name: string;
};

export type Product = {
    id: number;
    name: string;
    description: string;
    price_per_day: number;
    deposit: number;
    reviews_count: number;
    average_rating: number | null;
    category: ProductCategory;
};

export type ProductListResponse = {
    products: Product[];
};