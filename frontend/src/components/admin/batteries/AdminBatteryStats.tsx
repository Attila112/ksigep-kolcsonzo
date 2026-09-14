import Link from "next/link";

import { StatCard } from "@/components/ui/StatCard";
import type { AdminBatteryItem } from "@/types/adminBattery";

type AdminBatteryStatsProps = {
    items: AdminBatteryItem[];
    locale: string;

    activeType?: string;
    activeStatus?: string;

    labels: {
        total: string;
        batteries: string;
        chargers: string;
        available: string;
    };
};

export function AdminBatteryStats({
    items,
    locale,
    activeType,
    activeStatus,
    labels,
}: AdminBatteryStatsProps) {
    const total = items.length;

    const batteries = items.filter(
        (item) =>
            item.type === "BATTERY"
    ).length;

    const chargers = items.filter(
        (item) =>
            item.type === "CHARGER"
    ).length;

    const available = items.filter(
        (item) =>
            item.status === "AVAILABLE"
    ).length;

    const noFilter =
        !activeType &&
        !activeStatus;

    return (
        <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            <Link
                href={`/${locale}/admin/batteries`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.total}
                    value={total}
                    active={noFilter}
                />
            </Link>

            <Link
                href={`/${locale}/admin/batteries?type=BATTERY`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.batteries
                    }
                    value={batteries}
                    active={
                        activeType ===
                            "BATTERY" &&
                        !activeStatus
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/batteries?type=CHARGER`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.chargers
                    }
                    value={chargers}
                    active={
                        activeType ===
                            "CHARGER" &&
                        !activeStatus
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/batteries?status=AVAILABLE`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.available
                    }
                    value={available}
                    active={
                        activeStatus ===
                            "AVAILABLE" &&
                        !activeType
                    }
                />
            </Link>
        </div>
    );
}