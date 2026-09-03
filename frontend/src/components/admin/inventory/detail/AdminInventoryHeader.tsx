import { ArrowLeft } from "lucide-react";

import { Heading } from "@/components/ui/Heading";
import { StatusBadge } from "@/components/ui/StatusBadge";
import { Link } from "@/core/i18n/navigation";
import type { AdminInventoryDetail } from "@/types/adminInventory";

type AdminInventoryHeaderProps = {
    item: AdminInventoryDetail;

    labels: {
        back: string;

        statuses: {
            AVAILABLE: string;
            RENTED: string;
            INSPECTION: string;
            MAINTENANCE: string;
            DAMAGED: string;
            INACTIVE: string;
        };
    };
};

export function AdminInventoryHeader({
    item,
    labels,
}: AdminInventoryHeaderProps) {
    return (
        <>
            <Link
                href="/admin/inventory"
                className="mb-5 inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition-colors hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >
                <ArrowLeft
                    size={17}
                    aria-hidden="true"
                />

                {labels.back}
            </Link>

            <div className="mb-6">
                <div className="flex flex-wrap items-center gap-3">
                    <Heading level={1} size="xl">
                        {item.inventory_code}
                    </Heading>

                    <StatusBadge
                        status={item.status}
                        label={labels.statuses[item.status]}
                    />
                </div>

                <p className="mt-2 wrap-break-word text-sm text-slate-500 dark:text-slate-400">
                    {item.product.name}
                </p>
            </div>
        </>
    );
}