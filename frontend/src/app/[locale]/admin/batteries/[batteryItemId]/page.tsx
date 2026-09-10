import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminBatteryGeneralCard } from "@/components/admin/batteries/detail/AdminBatteryGeneralCard";
import { AdminBatteryHeader } from "@/components/admin/batteries/detail/AdminBatteryHeader";
import { AdminBatteryHistory } from "@/components/admin/batteries/detail/AdminBatteryHistory";
import { AdminBatteryStatusForm } from "@/components/admin/batteries/detail/AdminBatteryStatusForm";

import { getAdminBatteryItem, getAdminBatteryStatusHistory } from "@/services/adminBatteryService";


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

    const batteryItemIdNumber =
        Number(batteryItemId);

    const [
        { battery_item: item },
        { status_history: statusHistory },
    ] = await Promise.all([
        getAdminBatteryItem(
            batteryItemIdNumber
        ),
        getAdminBatteryStatusHistory(
            batteryItemIdNumber
        ),
    ]);

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
                <div className="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
                    <AdminBatteryStatusForm
                        batteryItemId={item.id}
                        currentStatus={item.status}
                        labels={{
                            title: t(
                                "batteries.detail.statusManagement"
                            ),
                            description: t(
                                "batteries.detail.statusManagementDescription"
                            ),
                            status: t(
                                "batteries.detail.status"
                            ),
                            adminNote: t(
                                "batteries.detail.adminNote"
                            ),
                            adminNoteRequired: t(
                                "batteries.detail.adminNoteRequired"
                            ),
                            save: t(
                                "batteries.detail.saveStatus"
                            ),
                            saving: t(
                                "batteries.detail.savingStatus"
                            ),
                            success: t(
                                "batteries.detail.statusUpdated"
                            ),
                            error: t(
                                "batteries.detail.statusUpdateError"
                            ),
                            rentedLocked: t(
                                "batteries.detail.rentedStatusLocked"
                            ),
                            statuses: {
                                AVAILABLE:
                                    statusLabels.AVAILABLE,
                                INSPECTION:
                                    statusLabels.INSPECTION,
                                MAINTENANCE:
                                    statusLabels.MAINTENANCE,
                                DAMAGED:
                                    statusLabels.DAMAGED,
                                INACTIVE:
                                    statusLabels.INACTIVE,
                            },
                        }}
                    />

                    <AdminBatteryHistory
                        history={statusHistory}
                        labels={{
                            title: t(
                                "batteries.detail.statusHistory"
                            ),
                            empty: t(
                                "batteries.detail.emptyStatusHistory"
                            ),
                            system: t(
                                "batteries.detail.systemUser"
                            ),
                            note: t(
                                "batteries.detail.historyNote"
                            ),
                            statuses: statusLabels,
                        }}
                    />
                </div>
            </div>
        </div>
    );
}