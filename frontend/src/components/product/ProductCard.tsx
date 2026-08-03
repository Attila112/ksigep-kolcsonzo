import { getTranslations } from "next-intl/server";
import type { Product } from "@/types/product";
import { formatCurrency } from "@/utils/formatCurrency";
import { Heading } from "@/components/ui/Heading";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";

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
    const common = await getTranslations("Common");

    return (
        <Card className="flex max-w-md flex-col gap-3">
            <p className="text-sm text-slate-500">
                {product.category.name}
            </p>

            <Heading level={2} size="md">
                {product.name}
            </Heading>

            <p className="text-slate-700">
                {product.description}
            </p>

            <div className="space-y-1">
                <p>
                    {t("dailyPrice")}:{" "}
                    <strong>
                        {formatCurrency(product.price_per_day)}
                    </strong>
                </p>

                <p>
                    {t("deposit")}:{" "}
                    {formatCurrency(product.deposit)}
                </p>
            </div>

            <p className="text-sm text-slate-500">
                {product.average_rating === null
                    ? t("noReviews")
                    : t("rating", {
                          rating: product.average_rating,
                          count: product.reviews_count,
                      })}
            </p>

            <Button className="mt-2 self-start">
                {common("actions.details")}
            </Button>
        </Card>
    );
}