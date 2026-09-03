import { StatCard } from "@/components/ui/StatCard";
import type { AdminInventoryItem } from "@/types/adminInventory";

type AdminInventoryStatsProps = {
    items: AdminInventoryItem[];
    labels: {
        total: string;
        available: string;
        rented: string;
        attention: string;
    };
};

export function AdminInventoryStats({
    items,
    labels,
}: AdminInventoryStatsProps) {
    const total = items.length;

    const available = items.filter(
        (item) => item.status === "AVAILABLE"
    ).length;

    const rented = items.filter(
        (item) => item.status === "RENTED"
    ).length;

    const attention = items.filter((item) =>
        [
            "INSPECTION",
            "MAINTENANCE",
            "DAMAGED",
        ].includes(item.status)
    ).length;

    return (
        <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            <StatCard title={labels.total} value={total} />

            <StatCard
                title={labels.available}
                value={available}
            />

            <StatCard
                title={labels.rented}
                value={rented}
            />

            <StatCard
                title={labels.attention}
                value={attention}
            />
        </div>
    );
}