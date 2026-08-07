import { setRequestLocale } from "next-intl/server";
import { getProduct } from "@/services/productService";
import { ProductDetailSection } from "@/components/product/ProductDetailSection";

type Props = {
    params: Promise<{
        locale: string;
        productId: string;
    }>;
};

export default async function ProductPage({
    params,
}: Props) {
    const { locale, productId } = await params;

    setRequestLocale(locale);

    const { product } = await getProduct(
        Number(productId)
    );

    return (
        <ProductDetailSection
            product={product}
        />
    );
}