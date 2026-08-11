import { ArrowLeft } from "lucide-react";

import { Heading } from "@/components/ui/Heading";
import { StatusBadge } from "@/components/ui/StatusBadge";
import { Link } from "@/core/i18n/navigation";
import type { AdminProductDetail } from "@/types/adminProduct";

type AdminProductHeaderProps = {
    product: AdminProductDetail;
    labels: {
        back: string;
        active: string;
        inactive: string;
    };
};

export function AdminProductHeader({
    product,
    labels,
}: AdminProductHeaderProps) {
    return (
        <>
            <Link
                href="/admin/products"
                className="mb-5 inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition-colors hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
            >
                <ArrowLeft size={17} aria-hidden="true" />
                {labels.back}
            </Link>

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-3">
                        <Heading level={1} size="xl">
                            {product.name}
                        </Heading>

                        <StatusBadge
                            status={product.active ? "ACTIVE" : "INACTIVE"}
                            label={
                                product.active
                                    ? labels.active
                                    : labels.inactive
                            }
                        />
                    </div>

                    <p className="mt-2 break-all text-sm text-slate-500 dark:text-slate-400">
                        {product.sku}
                    </p>
                </div>
            </div>
        </>
    );
}