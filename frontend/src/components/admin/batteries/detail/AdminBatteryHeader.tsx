import { Heading } from "@/components/ui/Heading";
import { StatusBadge } from "@/components/ui/StatusBadge";
import { Link } from "@/core/i18n/navigation";

import type {
    AdminBatteryDetail,
    AdminBatteryItemType,
    AdminBatteryStatus,
} from "@/types/adminBattery";

type AdminBatteryHeaderProps = {
    item: AdminBatteryDetail;

    labels: {
        back: string;

        types: Record<
            AdminBatteryItemType,
            string
        >;

        statuses: Record<
            AdminBatteryStatus,
            string
        >;
    };
};

export function AdminBatteryHeader({
    item,
    labels,
}: AdminBatteryHeaderProps) {
    return (
        <div className="min-w-0">
            <Link
                href="/admin/batteries"
                className="inline-flex text-sm font-medium text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >
                ← {labels.back}
            </Link>

            <div className="mt-4 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="min-w-0">
                    <Heading level={1} size="lg">
                        <span className="wrap-break-word">
                            {item.inventory_code}
                        </span>
                    </Heading>

                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {labels.types[item.type]}
                    </p>
                </div>

                <div className="shrink-0">
                    <StatusBadge
                        status={item.status}
                        label={
                            labels.statuses[
                            item.status
                            ]
                        }
                    />
                </div>
            </div>
        </div>
    );
}