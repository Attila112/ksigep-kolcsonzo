import {getTranslations, setRequestLocale,} from "next-intl/server";

import { AdminBatteryCard } from "@/components/admin/products/detail/AdminBatteryCard";
import { AdminInventoryTable } from "@/components/admin/products/detail/AdminInventoryTable";
import { AdminProductGeneralCard } from "@/components/admin/products/detail/AdminProductGeneralCard";
import { AdminProductHeader } from "@/components/admin/products/detail/AdminProductHeader";
import { AdminProductStats } from "@/components/admin/products/detail/AdminProductStats";
import { AdminWorkTypesCard } from "@/components/admin/products/detail/AdminWorkTypesCard";
import { getAdminProduct } from "@/services/adminProductService";
import { AdminProductDetailClient } from "@/components/admin/products/detail/AdminProductDetailClient";
import { getAdminBatterySystems, getAdminCategories, } from "@/services/adminLookupService";

type AdminProductDetailPageProps = {
    params: Promise<{
        locale: string;
        productId: string;
    }>;
};

export default async function AdminProductDetailPage({
    params,
}: AdminProductDetailPageProps) {
    const { locale, productId } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    const [
        { product },
        { categories },
        { battery_systems: batterySystems },
    ] = await Promise.all([
        getAdminProduct(Number(productId)),
        getAdminCategories(),
        getAdminBatterySystems(),
    ]);

    return (
        <div className="mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6">
            <AdminProductHeader
                product={product}
                labels={{
                    back: t("products.detail.back"),
                    active: t("status.active"),
                    inactive: t("status.inactive"),
                }}
            />
            <div className="mb-6">
                <AdminProductDetailClient
                    product={product}
                    categories={categories}
                    batterySystems={batterySystems}
                />
            </div>

            <AdminProductStats
                product={product}
                labels={{
                    total: t("products.detail.stats.total"),
                    available: t(
                        "products.detail.stats.available"
                    ),
                    rented: t(
                        "products.detail.stats.rented"
                    ),
                    maintenance: t(
                        "products.detail.stats.maintenance"
                    ),
                }}
            />

            <div className="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
                <div className="min-w-0 space-y-6">
                    <AdminProductGeneralCard
                        product={product}
                        labels={{
                            title: t(
                                "products.detail.generalInformation"
                            ),
                            sku: t("products.detail.sku"),
                            category: t(
                                "products.detail.category"
                            ),
                            dailyPrice: t(
                                "products.detail.dailyPrice"
                            ),
                            deposit: t(
                                "products.detail.deposit"
                            ),
                            description: t(
                                "products.detail.description"
                            ),
                        }}
                    />

                    <AdminInventoryTable
                        product={product}
                        labels={{
                            title: t(
                                "products.detail.inventory"
                            ),
                            inventoryCode: t(
                                "products.detail.inventoryCode"
                            ),
                            serialNumber: t(
                                "products.detail.serialNumber"
                            ),
                            status: t(
                                "products.detail.status"
                            ),
                            adminNote: t(
                                "products.detail.adminNote"
                            ),
                            noInventory: t(
                                "products.detail.noInventory"
                            ),
                            noSerialNumber: t(
                                "products.detail.noSerialNumber"
                            ),
                            noAdminNote: t(
                                "products.detail.noAdminNote"
                            ),
                        }}
                    />
                </div>

                <div className="min-w-0 space-y-6">
                    <AdminBatteryCard
                        product={product}
                        labels={{
                            title: t(
                                "products.detail.battery"
                            ),
                            batterySystem: t(
                                "products.detail.batterySystem"
                            ),
                            requiredBatteries: t(
                                "products.detail.requiredBatteries"
                            ),
                            requiredChargers: t(
                                "products.detail.requiredChargers"
                            ),
                            noBatterySystem: t(
                                "products.detail.noBatterySystem"
                            ),
                        }}
                    />

                    <AdminWorkTypesCard
                        product={product}
                        labels={{
                            title: t(
                                "products.detail.workTypes"
                            ),
                            empty: t(
                                "products.detail.noWorkTypes"
                            ),
                        }}
                    />
                </div>
            </div>
        </div>
    );
}