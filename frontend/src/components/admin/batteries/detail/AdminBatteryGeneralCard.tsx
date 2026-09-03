import { Heading } from "@/components/ui/Heading";

import type {
    AdminBatteryDetail,
    AdminBatteryItemType,
} from "@/types/adminBattery";

type AdminBatteryGeneralCardProps = {
    item: AdminBatteryDetail;

    labels: {
        title: string;
        inventoryCode: string;
        type: string;
        batterySystem: string;
        manufacturer: string;
        voltage: string;
        serialNumber: string;
        adminNote: string;
        noSerialNumber: string;
        noAdminNote: string;

        types: Record<
            AdminBatteryItemType,
            string
        >;
    };
};

export function AdminBatteryGeneralCard({
    item,
    labels,
}: AdminBatteryGeneralCardProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <Heading level={2} size="md">
                {labels.title}
            </Heading>

            <dl className="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.inventoryCode}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.inventory_code}
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.type}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {labels.types[item.type]}
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.batterySystem}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.battery_system.name}
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.manufacturer}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {
                            item.battery_system
                                .manufacturer
                        }
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.voltage}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {
                            item.battery_system
                                .voltage
                        }{" "}
                        V
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.serialNumber}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.serial_number ??
                            labels.noSerialNumber}
                    </dd>
                </div>
            </dl>

            <div className="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {labels.adminNote}
                </p>

                <p className="mt-2 whitespace-pre-wrap wrap-break-word text-slate-700 dark:text-slate-300">
                    {item.admin_note ??
                        labels.noAdminNote}
                </p>
            </div>
        </section>
    );
}