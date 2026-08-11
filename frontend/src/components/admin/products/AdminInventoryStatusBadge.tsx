"use client";

import { useTranslations } from "next-intl";

import { StatusBadge } from "@/components/ui/StatusBadge";
import type { AdminInventoryItem } from "@/types/adminProduct";

type AdminInventoryStatusBadgeProps = {
    status: AdminInventoryItem["status"];
};

export function AdminInventoryStatusBadge({
    status,
}: AdminInventoryStatusBadgeProps) {
    const t = useTranslations("Admin");

    const labels: Record<
        AdminInventoryItem["status"],
        string
    > = {
        AVAILABLE: t("inventoryStatus.available"),
        RENTED: t("inventoryStatus.rented"),
        INSPECTION: t("inventoryStatus.inspection"),
        MAINTENANCE: t("inventoryStatus.maintenance"),
        DAMAGED: t("inventoryStatus.damaged"),
        INACTIVE: t("inventoryStatus.inactive"),
    };

    return (
        <StatusBadge
            status={status}
            label={labels[status]}
        />
    );
}