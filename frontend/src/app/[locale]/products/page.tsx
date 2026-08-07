import { setRequestLocale } from "next-intl/server";
import { ProductCatalogSection } from "@/components/product/ProductCatalogSection";
import { getProducts } from "@/services/productService";

type ProductsPageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function ProductsPage({
    params,
}: ProductsPageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    const data = await getProducts();

    return (
        <ProductCatalogSection
            products={data.products}
        />
    );
}