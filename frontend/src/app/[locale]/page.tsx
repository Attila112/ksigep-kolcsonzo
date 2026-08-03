import { getTranslations, setRequestLocale } from "next-intl/server";
import { ProductList } from "@/components/product/ProductList";
import { getProducts } from "@/services/productService";

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
            <h1>{t("title")}</h1>

            <ProductList products={data.products} />
        </main>
    );
}