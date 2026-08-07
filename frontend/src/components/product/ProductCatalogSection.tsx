import { getTranslations } from "next-intl/server";
import { ProductList } from "@/components/product/ProductList";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Section } from "@/components/ui/Section";
import type { Product } from "@/types/product";

type ProductCatalogSectionProps = {
    products: Product[];
};

/**
 * A publikus termékkatalógus fő tartalmi blokkja.
 *
 * Később ide kerülhet:
 * - keresés
 * - kategóriaszűrés
 * - rendezés
 * - lapozás
 */
export async function ProductCatalogSection({
    products,
}: ProductCatalogSectionProps) {
    const t = await getTranslations("Product");

    return (
        <Section>
            <Container>
                <Heading level={1} size="xl">
                    {t("catalog.title")}
                </Heading>

                <p className="mt-4 max-w-2xl text-slate-600">
                    {t("catalog.description")}
                </p>

                <div className="mt-8">
                    <ProductList products={products} />
                </div>
            </Container>
        </Section>
    );
}