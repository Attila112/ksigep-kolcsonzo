import { getTranslations, setRequestLocale } from "next-intl/server";

import { AdminProductStats } from "@/components/admin/products/AdminProductStats";
import { AdminProductTable } from "@/components/admin/products/AdminProductTable";
import { AdminProductToolbar } from "@/components/admin/products/AdminProductToolbar";
import { Heading } from "@/components/ui/Heading";
import { getAdminProducts } from "@/services/adminProductService";

type AdminProductsPageProps = {
    params: Promise<{
        locale: string;
    }>;
    searchParams: Promise<{
        filter?: string;
    }>;
};

export default async function AdminProductsPage({
    params,
    searchParams,
}: AdminProductsPageProps) {
    const { locale } = await params;
    const { filter } = await searchParams;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");
    const data = await getAdminProducts();

    const filteredProducts =
        filter === "ACTIVE"
            ? data.products.filter(
                (product) =>
                    product.active
            )
            : filter === "HAS_INVENTORY"
                ? data.products.filter(
                    (product) =>
                        product.inventory_items_count >
                        0
                )
                : filter === "AVAILABLE"
                    ? data.products.filter(
                        (product) =>
                            product.available_inventory_count >
                            0
                    )
                    : filter === "BATTERY"
                        ? data.products.filter(
                            (product) =>
                                product.battery_system !==
                                null
                        )
                        : data.products;

    return (
        <div className="p-4 lg:p-6">
            <div className="mb-6">
                <Heading level={1} size="lg">
                    {t("products.title")}
                </Heading>

                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {t("products.description")}
                </p>
            </div>

            <AdminProductStats
                products={data.products}
                locale={locale}
                activeFilter={filter}
                labels={{
                    activeProducts: t("products.stats.activeProducts"),
                    totalInventory: t("products.stats.totalInventory"),
                    availableInventory: t("products.stats.availableInventory"),
                    batteryProducts: t("products.stats.batteryProducts"),
                }}
            />

            <AdminProductToolbar
                searchPlaceholder={t("products.searchPlaceholder")}
            />

            <AdminProductTable
                products={filteredProducts}
                labels={{
                    columns: {
                        image: t("products.columns.image"),
                        name: t("products.columns.name"),
                        sku: t("products.columns.sku"),
                        category: t("products.columns.category"),
                        stock: t("products.columns.stock"),
                        available: t("products.columns.available"),
                        price: t("products.columns.price"),
                        status: t("products.columns.status"),
                        action: t("products.columns.action"),
                    },
                    active: t("status.active"),
                    inactive: t("status.inactive"),
                    open: t("products.open"),
                }}
            />
        </div>
    );
}