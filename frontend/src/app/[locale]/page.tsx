import { getTranslations, setRequestLocale } from "next-intl/server";
import { ProductList } from "@/components/product/ProductList";
import { getProducts } from "@/services/productService";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";

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
        <main>
            <Container className="py-8 sm:py-12">
                <Heading level={1} size="xl">
                    {t("title")}
                </Heading>
                <div className="mt-6">
                    <ProductList products={data.products} />
                </div>
            </Container>
        </main>
    );
}