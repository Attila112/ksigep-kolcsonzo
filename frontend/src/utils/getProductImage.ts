const PRODUCT_PLACEHOLDER =
    "/images/products/placeholder.png";

export function getProductImage(
    imagePath: string | null
): string {
    if (!imagePath) {
        return PRODUCT_PLACEHOLDER;
    }

    return imagePath.startsWith("/")
        ? imagePath
        : `/${imagePath}`;
}