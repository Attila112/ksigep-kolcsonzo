import { Heading } from "@/components/ui/Heading";
import type { AdminProductDetail } from "@/types/adminProduct";

type AdminBatteryCardProps = {
    product: AdminProductDetail;
    labels: {
        title: string;
        batterySystem: string;
        requiredBatteries: string;
        requiredChargers: string;
        noBatterySystem: string;
    };
};

export function AdminBatteryCard({
    product,
    labels,
}: AdminBatteryCardProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <Heading level={2} size="md">
                {labels.title}
            </Heading>

            {product.battery_system ? (
                <dl className="mt-5 grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                    <div>
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.batterySystem}
                        </dt>

                        <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                            {product.battery_system.manufacturer}{" "}
                            {product.battery_system.name}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.requiredBatteries}
                        </dt>

                        <dd className="mt-1 font-medium text-slate-950 dark:text-white">
                            {product.required_batteries}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.requiredChargers}
                        </dt>

                        <dd className="mt-1 font-medium text-slate-950 dark:text-white">
                            {product.required_chargers}
                        </dd>
                    </div>
                </dl>
            ) : (
                <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    {labels.noBatterySystem}
                </p>
            )}
        </section>
    );
}