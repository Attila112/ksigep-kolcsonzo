import Link from "next/link";

import type {
    AdminProduct,
} from "@/types/adminProduct";

import { StatCard } from "@/components/ui/StatCard";

type AdminProductStatsProps = {
    products: AdminProduct[];
    locale: string;
    activeFilter?: string;

    labels: {
        activeProducts: string;
        totalInventory: string;
        availableInventory: string;
        batteryProducts: string;
    };
};

export function AdminProductStats({
    products,
    locale,
    activeFilter,
    labels,
}: AdminProductStatsProps) {
    const activeProducts =
        products.filter(
            (product) =>
                product.active
        ).length;

    const totalInventory =
        products.reduce(
            (sum, product) =>
                sum +
                product.inventory_items_count,
            0
        );

    const availableInventory =
        products.reduce(
            (sum, product) =>
                sum +
                product.available_inventory_count,
            0
        );

    const batteryProducts =
        products.filter(
            (product) =>
                product.battery_system !== null
        ).length;

    return (
        <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link
                href={`/${locale}/admin/products?filter=ACTIVE`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.activeProducts
                    }
                    value={
                        activeProducts
                    }
                    active={
                        activeFilter ===
                        "ACTIVE"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/products?filter=HAS_INVENTORY`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.totalInventory
                    }
                    value={
                        totalInventory
                    }
                    active={
                        activeFilter ===
                        "HAS_INVENTORY"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/products?filter=AVAILABLE`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.availableInventory
                    }
                    value={
                        availableInventory
                    }
                    active={
                        activeFilter ===
                        "AVAILABLE"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/products?filter=BATTERY`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={
                        labels.batteryProducts
                    }
                    value={
                        batteryProducts
                    }
                    active={
                        activeFilter ===
                        "BATTERY"
                    }
                />
            </Link>
        </div>
    );
}