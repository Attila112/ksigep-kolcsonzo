import { getTranslations } from "next-intl/server";
import { ProductCard } from "@/components/product/ProductCard";
import type { Product } from "@/types/product";

type ProductListProps = {
    products: Product[];
};

/**
 * Termékek listáját jeleníti meg ProductCard komponensekkel.
 */
export async function ProductList({
    products,
}: ProductListProps) {
    const t = await getTranslations("Home");

    if (products.length === 0) {
        return <p>{t("noProducts")}</p>;
    }

    return (
        <section>
            {products.map((product) => (
                <ProductCard
                    key={product.id}
                    product={product}
                />
            ))}
        </section>
    );
}