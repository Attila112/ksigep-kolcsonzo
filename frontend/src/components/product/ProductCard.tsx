import { getTranslations } from "next-intl/server";
import type { Product } from "@/types/product";
import { formatCurrency } from "@/utils/formatCurrency";
import { Heading } from "@/components/ui/Heading";
import { Card } from "@/components/ui/Card";
import { ButtonLink } from "../ui/ButtonLink";
import Image from "next/image";
import { getProductImage } from "@/utils/getProductImage";

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
            <div className="relative aspect-4/3 w-full overflow-hidden rounded-lg bg-slate-100">
                <Image
                    src={getProductImage(product.image_path)}
                    alt={product.name}
                    fill
                    className="object-cover"
                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                />
            </div>
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

            <ButtonLink
                href={`/products/${product.id}`}
                className="mt-2 self-start"
            >
                {common("actions.details")}
            </ButtonLink>
        </Card>
    );
}