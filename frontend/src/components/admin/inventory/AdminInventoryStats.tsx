import Link from "next/link";

import { StatCard } from "@/components/ui/StatCard";
import type { AdminInventoryItem } from "@/types/adminInventory";

type AdminInventoryStatsProps = {
    items: AdminInventoryItem[];
    locale: string;
    activeStatus?: string;

    labels: {
        total: string;
        available: string;
        rented: string;
        attention: string;
    };
};

export function AdminInventoryStats({
    items,
    locale,
    activeStatus,
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
            <Link
                href={`/${locale}/admin/inventory`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.total}
                    value={total}
                    active={!activeStatus}
                />
            </Link>

            <Link
                href={`/${locale}/admin/inventory?status=AVAILABLE`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.available}
                    value={available}
                    active={
                        activeStatus ===
                        "AVAILABLE"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/inventory?status=RENTED`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.rented}
                    value={rented}
                    active={
                        activeStatus ===
                        "RENTED"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/inventory?status=ATTENTION`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.attention}
                    value={attention}
                    active={
                        activeStatus ===
                        "ATTENTION"
                    }
                />
            </Link>
        </div>
    );
}