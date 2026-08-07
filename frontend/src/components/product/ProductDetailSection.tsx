import { getTranslations } from "next-intl/server";

import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Section } from "@/components/ui/Section";
import type { Product } from "@/types/product";
import { formatCurrency } from "@/utils/formatCurrency";
import Image from "next/image";
import { getProductImage } from "@/utils/getProductImage";

type ProductDetailSectionProps = {
    product: Product;
};

export async function ProductDetailSection({
    product,
}: ProductDetailSectionProps) {
    const t = await getTranslations("Product");

    return (
        <Section>
            <Container>
                <div className="grid gap-8 lg:grid-cols-2 lg:gap-12">
                    <div className="relative aspect-4/3 overflow-hidden rounded-xl bg-slate-100">
                        <Image
                            src={getProductImage(product.image_path)}
                            alt={product.name}
                            fill
                            priority
                            className="object-cover"
                            sizes="(max-width: 1024px) 100vw, 50vw"
                        />
                    </div>

                    <div>
                        <p className="text-sm text-slate-500">
                            {product.category.name}
                        </p>

                        <Heading
                            level={1}
                            size="xl"
                            className="mt-2"
                        >
                            {product.name}
                        </Heading>

                        <p className="mt-6 text-slate-600">
                            {product.description}
                        </p>

                        <div className="mt-8 space-y-2">
                            <p>
                                <strong>
                                    {t("details.dailyPrice")}:
                                </strong>{" "}
                                {formatCurrency(product.price_per_day)}
                            </p>

                            <p>
                                <strong>
                                    {t("details.deposit")}:
                                </strong>{" "}
                                {formatCurrency(product.deposit)}
                            </p>
                        </div>
                    </div>
                    {product.battery_system && (
                        <div className="mt-10 rounded-xl border border-slate-200 bg-white p-5">
                            <Heading level={2} size="md">
                                {t("details.packageContents")}
                            </Heading>

                            <p className="mt-4 text-sm text-slate-500">
                                {t("details.batterySystem")}
                            </p>

                            <p className="font-medium">
                                {product.battery_system.manufacturer}{" "}
                                {product.battery_system.name}
                            </p>

                            <div className="mt-4 space-y-2 text-slate-700">
                                {product.required_batteries > 0 && (
                                    <p>
                                        ✓ {product.required_batteries} db{" "}
                                        {t("details.battery")}
                                    </p>
                                )}

                                {product.required_chargers > 0 && (
                                    <p>
                                        ✓ {product.required_chargers} db{" "}
                                        {t("details.charger")}
                                    </p>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </Container>
        </Section>
    );
}