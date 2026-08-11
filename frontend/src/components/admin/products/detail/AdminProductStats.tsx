import { StatCard } from "@/components/ui/StatCard";
import type { AdminProductDetail } from "@/types/adminProduct";

type AdminProductStatsProps = {
    product: AdminProductDetail;
    labels: {
        total: string;
        available: string;
        rented: string;
        maintenance: string;
    };
};

export function AdminProductStats({
    product,
    labels,
}: AdminProductStatsProps) {
    return (
        <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            <StatCard
                title={labels.total}
                value={product.inventory_items_count}
            />

            <StatCard
                title={labels.available}
                value={product.available_inventory_count}
            />

            <StatCard
                title={labels.rented}
                value={product.rented_inventory_count}
            />

            <StatCard
                title={labels.maintenance}
                value={product.maintenance_inventory_count}
            />
        </div>
    );
}