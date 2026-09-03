import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminBatteryGeneralCard } from "@/components/admin/batteries/detail/AdminBatteryGeneralCard";
import { AdminBatteryHeader } from "@/components/admin/batteries/detail/AdminBatteryHeader";

import { getAdminBatteryItem } from "@/services/adminBatteryService";

type AdminBatteryDetailPageProps = {
    params: Promise<{
        locale: string;
        batteryItemId: string;
    }>;
};

export default async function AdminBatteryDetailPage({
    params,
}: AdminBatteryDetailPageProps) {
    const {
        locale,
        batteryItemId,
    } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    const { battery_item: item } =
        await getAdminBatteryItem(
            Number(batteryItemId)
        );

    const typeLabels = {
        BATTERY: t(
            "batteries.types.battery"
        ),
        CHARGER: t(
            "batteries.types.charger"
        ),
    };

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
            <AdminBatteryHeader
                item={item}
                labels={{
                    back: t(
                        "batteries.detail.back"
                    ),
                    types: typeLabels,
                    statuses: statusLabels,
                }}
            />

            <div className="mt-6">
                <AdminBatteryGeneralCard
                    item={item}
                    labels={{
                        title: t(
                            "batteries.detail.generalInformation"
                        ),
                        inventoryCode: t(
                            "batteries.detail.inventoryCode"
                        ),
                        type: t(
                            "batteries.detail.type"
                        ),
                        batterySystem: t(
                            "batteries.detail.batterySystem"
                        ),
                        manufacturer: t(
                            "batteries.detail.manufacturer"
                        ),
                        voltage: t(
                            "batteries.detail.voltage"
                        ),
                        serialNumber: t(
                            "batteries.detail.serialNumber"
                        ),
                        adminNote: t(
                            "batteries.detail.adminNote"
                        ),
                        noSerialNumber: t(
                            "batteries.detail.noSerialNumber"
                        ),
                        noAdminNote: t(
                            "batteries.detail.noAdminNote"
                        ),
                        types: typeLabels,
                    }}
                />
            </div>
        </div>
    );
}