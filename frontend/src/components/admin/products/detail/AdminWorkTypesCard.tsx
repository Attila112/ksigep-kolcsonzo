import { Heading } from "@/components/ui/Heading";
import type { AdminProductDetail } from "@/types/adminProduct";

type AdminWorkTypesCardProps = {
    product: AdminProductDetail;
    labels: {
        title: string;
        empty: string;
    };
};

export function AdminWorkTypesCard({
    product,
    labels,
}: AdminWorkTypesCardProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <Heading level={2} size="md">
                {labels.title}
            </Heading>

            {product.work_types.length > 0 ? (
                <div className="mt-4 flex flex-wrap gap-2">
                    {product.work_types.map((workType) => (
                        <span
                            key={workType.id}
                            className="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {workType.name}
                        </span>
                    ))}
                </div>
            ) : (
                <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    {labels.empty}
                </p>
            )}
        </section>
    );
}