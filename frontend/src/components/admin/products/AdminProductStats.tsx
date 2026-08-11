import type { AdminProduct } from "@/types/adminProduct";
import { StatCard } from "@/components/ui/StatCard";

type AdminProductStatsProps = {
    products: AdminProduct[];
    labels: {
        activeProducts: string;
        totalInventory: string;
        availableInventory: string;
        batteryProducts: string;
    };
};

export function AdminProductStats({
    products,
    labels,
}: AdminProductStatsProps) {
    const activeProducts = products.filter(
        (product) => product.active
    ).length;

    const totalInventory = products.reduce(
        (sum, product) => sum + product.inventory_items_count,
        0
    );

    const availableInventory = products.reduce(
        (sum, product) => sum + product.available_inventory_count,
        0
    );

    const batteryProducts = products.filter(
        (product) => product.battery_system !== null
    ).length;

    return (
        <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard
                title={labels.activeProducts}
                value={activeProducts}
            />

            <StatCard
                title={labels.totalInventory}
                value={totalInventory}
            />

            <StatCard
                title={labels.availableInventory}
                value={availableInventory}
            />

            <StatCard
                title={labels.batteryProducts}
                value={batteryProducts}
            />
        </div>
    );
}