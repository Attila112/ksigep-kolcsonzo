import { StatCard } from "@/components/ui/StatCard";
import type { AdminBatteryItem } from "@/types/adminBattery";

type AdminBatteryStatsProps = {
    items: AdminBatteryItem[];
    labels: {
        total: string;
        batteries: string;
        chargers: string;
        available: string;
    };
};

export function AdminBatteryStats({
    items,
    labels,
}: AdminBatteryStatsProps) {
    const total = items.length;

    const batteries = items.filter(
        (item) => item.type === "BATTERY"
    ).length;

    const chargers = items.filter(
        (item) => item.type === "CHARGER"
    ).length;

    const available = items.filter(
        (item) => item.status === "AVAILABLE"
    ).length;

    return (
        <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            <StatCard
                title={labels.total}
                value={total}
            />

            <StatCard
                title={labels.batteries}
                value={batteries}
            />

            <StatCard
                title={labels.chargers}
                value={chargers}
            />

            <StatCard
                title={labels.available}
                value={available}
            />
        </div>
    );
}