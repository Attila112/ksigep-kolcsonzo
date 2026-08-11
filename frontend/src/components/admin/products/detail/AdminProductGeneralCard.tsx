import Image from "next/image";

import { Heading } from "@/components/ui/Heading";
import type { AdminProductDetail } from "@/types/adminProduct";
import { formatCurrency } from "@/utils/formatCurrency";
import { getProductImage } from "@/utils/getProductImage";

type AdminProductGeneralCardProps = {
    product: AdminProductDetail;
    labels: {
        title: string;
        sku: string;
        category: string;
        dailyPrice: string;
        deposit: string;
        description: string;
    };
};

export function AdminProductGeneralCard({
    product,
    labels,
}: AdminProductGeneralCardProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <Heading level={2} size="md">
                {labels.title}
            </Heading>

            <div className="mt-5 grid gap-6 md:grid-cols-[160px_minmax(0,1fr)] lg:grid-cols-[180px_minmax(0,1fr)]">
                <div className="mx-auto w-full max-w-55 md:mx-0 md:max-w-none">
                    <div className="relative aspect-4/3 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-900">
                        <Image
                            src={getProductImage(product.image_path)}
                            alt={product.name}
                            fill
                            priority
                            className="object-cover"
                            sizes="(max-width: 767px) 220px, 180px"
                        />
                    </div>
                </div>

                <dl className="grid min-w-0 gap-5 sm:grid-cols-2">
                    <div className="min-w-0">
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.sku}
                        </dt>

                        <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                            {product.sku ?? "—"}
                        </dd>
                    </div>

                    <div className="min-w-0">
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.category}
                        </dt>

                        <dd className="mt-1 wrap-break-word font-medium text-slate-950 dark:text-white">
                            {product.category.name}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.dailyPrice}
                        </dt>

                        <dd className="mt-1 font-medium text-slate-950 dark:text-white">
                            {formatCurrency(
                                Number(product.price_per_day)
                            )}
                        </dd>
                    </div>

                    <div>
                        <dt className="text-sm text-slate-500 dark:text-slate-400">
                            {labels.deposit}
                        </dt>

                        <dd className="mt-1 font-medium text-slate-950 dark:text-white">
                            {formatCurrency(
                                Number(product.deposit)
                            )}
                        </dd>
                    </div>
                </dl>
            </div>

            <div className="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {labels.description}
                </p>

                <p className="mt-2 wrap-break-word leading-7 text-slate-700 dark:text-slate-300">
                    {product.description}
                </p>
            </div>
        </section>
    );
}