import { getTranslations, setRequestLocale } from "next-intl/server";
import { ProductList } from "@/components/product/ProductList";
import { getProducts } from "@/services/productService";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Hero } from "@/components/home/Hero";
import { Section } from "@/components/ui/Section";
import { WorkTypeSection } from "@/components/home/WorkTypeSection";
import { getWorkTypes } from "@/services/workTypeService";

type HomePageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function HomePage({
    params,
}: HomePageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Home");
    const [productData, workTypeData] = await Promise.all([
        getProducts(),
        getWorkTypes(),
    ]);

    return (
        <>
            <Hero />

            <WorkTypeSection
                workTypes={workTypeData.work_types}
            />

            <Section>
                <Container>
                    <Heading level={2} size="lg">
                        {t("featuredProducts.title")}
                    </Heading>

                    <div className="mt-6">
                        <ProductList
                            products={productData.products}
                        />
                    </div>
                </Container>
            </Section>
        </>
    );
}