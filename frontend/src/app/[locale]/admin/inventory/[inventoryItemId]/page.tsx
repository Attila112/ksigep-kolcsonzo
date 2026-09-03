import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminInventoryStatusForm } from "@/components/admin/inventory/detail/AdminInventoryStatusForm";
import { AdminInventoryGeneralCard } from "@/components/admin/inventory/detail/AdminInventoryGeneralCard";
import { AdminInventoryHeader } from "@/components/admin/inventory/detail/AdminInventoryHeader";
import { AdminInventoryHistory } from "@/components/admin/inventory/detail/AdminInventoryHistory";

import {
    getAdminInventoryItem,
    getAdminInventoryStatusHistory,
} from "@/services/adminInventoryService";

type AdminInventoryDetailPageProps = {
    params: Promise<{
        locale: string;
        inventoryItemId: string;
    }>;
};

export default async function AdminInventoryDetailPage({
    params,
}: AdminInventoryDetailPageProps) {
    const { locale, inventoryItemId } =
        await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    const [
        { inventory_item: item },
        { status_history: history },
    ] = await Promise.all([
        getAdminInventoryItem(
            Number(inventoryItemId)
        ),

        getAdminInventoryStatusHistory(
            Number(inventoryItemId)
        ),
    ]);

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
        <div className="mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6">
            <AdminInventoryHeader
                item={item}
                labels={{
                    back: t(
                        "inventory.detail.back"
                    ),
                    statuses: statusLabels,
                }}
            />

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(340px,0.7fr)]">
                <div className="min-w-0">
                    <AdminInventoryGeneralCard
                        item={item}
                        labels={{
                            title: t(
                                "inventory.detail.generalInformation"
                            ),
                            inventoryCode: t(
                                "inventory.detail.inventoryCode"
                            ),
                            serialNumber: t(
                                "inventory.detail.serialNumber"
                            ),
                            product: t(
                                "inventory.detail.product"
                            ),
                            sku: t(
                                "inventory.detail.sku"
                            ),
                            category: t(
                                "inventory.detail.category"
                            ),
                            adminNote: t(
                                "inventory.detail.adminNote"
                            ),
                            noSerialNumber: t(
                                "inventory.detail.noSerialNumber"
                            ),
                            noAdminNote: t(
                                "inventory.detail.noAdminNote"
                            ),
                            batterySystem: t(
                                "inventory.detail.batterySystem"
                            ),
                            requiredBatteries: t(
                                "inventory.detail.requiredBatteries"
                            ),
                            requiredChargers: t(
                                "inventory.detail.requiredChargers"
                            ),
                            noBatterySystem: t(
                                "inventory.detail.noBatterySystem"
                            ),
                        }}
                    />
                    <AdminInventoryStatusForm
                        item={item}
                    />
                </div>

                <div className="min-w-0">
                    <AdminInventoryHistory
                        history={history}
                        labels={{
                            title: t(
                                "inventory.detail.history"
                            ),
                            empty: t(
                                "inventory.detail.noHistory"
                            ),
                            system: t(
                                "inventory.detail.system"
                            ),
                            noNote: t(
                                "inventory.detail.noHistoryNote"
                            ),
                            statuses: statusLabels,
                        }}
                    />
                </div>
            </div>
        </div>
    );
}