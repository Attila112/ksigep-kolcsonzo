import { getTranslations, setRequestLocale } from "next-intl/server";
import { ProductList } from "@/components/product/ProductList";
import { getProducts } from "@/services/productService";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Hero } from "@/components/home/Hero";
import { Section } from "@/components/ui/Section";

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
    const data = await getProducts();

    return (
        <>
            <Hero />

            <Section className="pt-0">
                <Container>
                    <Heading
                        level={2}
                        size="lg"
                    >
                        {t("featuredProducts.title")}
                    </Heading>

                    <div className="mt-6">
                        <ProductList products={data.products} />
                    </div>
                </Container>
            </Section>
        </>
    );
}