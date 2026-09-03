import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminInventoryStats } from "@/components/admin/inventory/AdminInventoryStats";
import { AdminInventoryTable } from "@/components/admin/inventory/AdminInventoryTable";
import { Heading } from "@/components/ui/Heading";

import { getAdminInventoryItems } from "@/services/adminInventoryService";

type AdminInventoryPageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function AdminInventoryPage({
    params,
}: AdminInventoryPageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    const { inventory_items: items } =
        await getAdminInventoryItems();

    return (
        <div className="mx-auto w-full max-w-[1800px] p-4 sm:p-5 lg:p-6">
            <div className="mb-6">
                <Heading level={1} size="lg">
                    {t("inventory.title")}
                </Heading>

                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {t("inventory.description")}
                </p>
            </div>

            <AdminInventoryStats
                items={items}
                labels={{
                    total: t("inventory.stats.total"),
                    available: t(
                        "inventory.stats.available"
                    ),
                    rented: t(
                        "inventory.stats.rented"
                    ),
                    attention: t(
                        "inventory.stats.attention"
                    ),
                }}
            />

            <AdminInventoryTable
                items={items}
                labels={{
                    columns: {
                        inventoryCode: t(
                            "inventory.columns.inventoryCode"
                        ),
                        product: t(
                            "inventory.columns.product"
                        ),
                        sku: t(
                            "inventory.columns.sku"
                        ),
                        category: t(
                            "inventory.columns.category"
                        ),
                        serialNumber: t(
                            "inventory.columns.serialNumber"
                        ),
                        status: t(
                            "inventory.columns.status"
                        ),
                        adminNote: t(
                            "inventory.columns.adminNote"
                        ),
                        action: t(
                            "inventory.columns.action"
                        ),
                    },

                    statuses: {
                        AVAILABLE: t(
                            "inventoryStatus.available"
                        ),
                        RENTED: t(
                            "inventoryStatus.rented"
                        ),
                        INSPECTION: t(
                            "inventoryStatus.inspection"
                        ),
                        MAINTENANCE: t(
                            "inventoryStatus.maintenance"
                        ),
                        DAMAGED: t(
                            "inventoryStatus.damaged"
                        ),
                        INACTIVE: t(
                            "inventoryStatus.inactive"
                        ),
                    },

                    open: t("inventory.open"),

                    noSerialNumber: t(
                        "inventory.noSerialNumber"
                    ),

                    noAdminNote: t(
                        "inventory.noAdminNote"
                    ),

                    empty: t("inventory.empty"),
                }}
            />
        </div>
    );
}