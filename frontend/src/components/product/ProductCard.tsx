import { getTranslations } from "next-intl/server";
import type { Product } from "@/types/product";
import { formatCurrency } from "@/utils/formatCurrency";

type ProductCardProps = {
    product: Product;
};

/**
 * Egyetlen termék rövid adatait jeleníti meg.
 */
export async function ProductCard({
    product,
}: ProductCardProps) {
    const t = await getTranslations("Product");

    return (
        <article>
            <p>{product.category.name}</p>

            <h2>{product.name}</h2>

            <p>{product.description}</p>

            <p>
                {t("dailyPrice")}:{" "}
                <strong>
                    {formatCurrency(product.price_per_day)}
                </strong>
            </p>

            <p>
                {t("deposit")}: {formatCurrency(product.deposit)}
            </p>

            <p>
                {product.average_rating === null
                    ? t("noReviews")
                    : t("rating", {
                          rating: product.average_rating,
                          count: product.reviews_count,
                      })}
            </p>
        </article>
    );
}