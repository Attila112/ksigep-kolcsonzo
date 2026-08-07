export type ProductCategory = {
    id: number;
    name: string;
};

export type Product = {
    id: number;
    name: string;
    description: string;
    image_path: string | null;
    price_per_day: number;
    deposit: number;
    reviews_count: number;
    average_rating: number | null;
    category: ProductCategory;
    battery_system: {
        id: number;
        manufacturer: string;
        name: string;
        voltage: string;
    } | null;

    required_batteries: number;
    required_chargers: number;
};

export type ProductListResponse = {
    products: Product[];
};

export type ProductDetailResponse = {
    product: Product;
};