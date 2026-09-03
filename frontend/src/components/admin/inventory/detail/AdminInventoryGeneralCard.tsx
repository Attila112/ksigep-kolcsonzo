import { Heading } from "@/components/ui/Heading";
import type { AdminInventoryDetail } from "@/types/adminInventory";

type AdminInventoryGeneralCardProps = {
    item: AdminInventoryDetail;

    labels: {
        title: string;
        inventoryCode: string;
        serialNumber: string;
        product: string;
        sku: string;
        category: string;
        adminNote: string;
        noSerialNumber: string;
        noAdminNote: string;
        batterySystem: string;
        requiredBatteries: string;
        requiredChargers: string;
        noBatterySystem: string;
    };
};

export function AdminInventoryGeneralCard({
    item,
    labels,
}: AdminInventoryGeneralCardProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <Heading level={2} size="md">
                {labels.title}
            </Heading>

            <dl className="mt-5 grid gap-5 sm:grid-cols-2">
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
                        {labels.serialNumber}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.serial_number ??
                            labels.noSerialNumber}
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.product}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.product.name}
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.sku}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.product.sku ?? "—"}
                    </dd>
                </div>

                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.category}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.product.category.name}
                    </dd>
                </div>
                <div className="min-w-0">
                    <dt className="text-sm text-slate-500 dark:text-slate-400">
                        {labels.batterySystem}
                    </dt>

                    <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                        {item.product.battery_system
                            ? `${item.product.battery_system.manufacturer} ${item.product.battery_system.name}`
                            : labels.noBatterySystem}
                    </dd>
                </div>

                {item.product.battery_system && (
                    <>
                        <div className="min-w-0">
                            <dt className="text-sm text-slate-500 dark:text-slate-400">
                                {labels.requiredBatteries}
                            </dt>

                            <dd className="mt-1 font-medium text-slate-950 dark:text-white">
                                {item.product.required_batteries}
                            </dd>
                        </div>

                        <div className="min-w-0">
                            <dt className="text-sm text-slate-500 dark:text-slate-400">
                                {labels.requiredChargers}
                            </dt>

                            <dd className="mt-1 font-medium text-slate-950 dark:text-white">
                                {item.product.required_chargers}
                            </dd>
                        </div>
                    </>
                )}
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