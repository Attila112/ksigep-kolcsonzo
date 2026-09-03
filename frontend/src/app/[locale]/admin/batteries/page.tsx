import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminBatteryStats } from "@/components/admin/batteries/AdminBatteryStats";
import { AdminBatteryTable } from "@/components/admin/batteries/AdminBatteryTable";
import { Heading } from "@/components/ui/Heading";

import { getAdminBatteryItems } from "@/services/adminBatteryService";

type AdminBatteriesPageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function AdminBatteriesPage({
    params,
}: AdminBatteriesPageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    const { battery_items: items } =
        await getAdminBatteryItems();

    const statusLabels = {
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
    };

    return (
        <div className="mx-auto w-full max-w-[1800px] p-4 sm:p-5 lg:p-6">
            <div className="mb-6">
                <Heading level={1} size="lg">
                    {t("batteries.title")}
                </Heading>

                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {t("batteries.description")}
                </p>
            </div>

            <AdminBatteryStats
                items={items}
                labels={{
                    total: t(
                        "batteries.stats.total"
                    ),
                    batteries: t(
                        "batteries.stats.batteries"
                    ),
                    chargers: t(
                        "batteries.stats.chargers"
                    ),
                    available: t(
                        "batteries.stats.available"
                    ),
                }}
            />

            <AdminBatteryTable
                items={items}
                labels={{
                    columns: {
                        inventoryCode: t(
                            "batteries.columns.inventoryCode"
                        ),
                        type: t(
                            "batteries.columns.type"
                        ),
                        system: t(
                            "batteries.columns.system"
                        ),
                        manufacturer: t(
                            "batteries.columns.manufacturer"
                        ),
                        voltage: t(
                            "batteries.columns.voltage"
                        ),
                        serialNumber: t(
                            "batteries.columns.serialNumber"
                        ),
                        status: t(
                            "batteries.columns.status"
                        ),
                        action: t(
                            "batteries.columns.action"
                        ),
                    },

                    types: {
                        BATTERY: t(
                            "batteries.types.battery"
                        ),
                        CHARGER: t(
                            "batteries.types.charger"
                        ),
                    },

                    statuses: statusLabels,

                    noSerialNumber: t(
                        "batteries.noSerialNumber"
                    ),

                    open: t(
                        "batteries.open"
                    ),

                    empty: t(
                        "batteries.empty"
                    ),
                }}
            />
        </div>
    );
}