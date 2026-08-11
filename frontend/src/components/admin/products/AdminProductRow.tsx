import Image from "next/image";

import type { AdminProduct } from "@/types/adminProduct";
import { ButtonLink } from "@/components/ui/ButtonLink";
import { DataTableCell, DataTableRow } from "@/components/ui/DataTable";
import { StatusBadge } from "@/components/ui/StatusBadge";
import { formatCurrency } from "@/utils/formatCurrency";
import { getProductImage } from "@/utils/getProductImage";

type AdminProductRowProps = {
    product: AdminProduct;
    labels: {
        active: string;
        inactive: string;
        open: string;
    };
};

export function AdminProductRow({
    product,
    labels,
}: AdminProductRowProps) {
    return (
        <DataTableRow>
            <DataTableCell>
                <div className="relative h-12 w-16 overflow-hidden rounded-md bg-slate-100 dark:bg-slate-900">
                    <Image
                        src={getProductImage(product.image_path)}
                        alt={product.name}
                        fill
                        className="object-cover"
                        sizes="64px"
                    />
                </div>
            </DataTableCell>

            <DataTableCell className="font-medium text-slate-950 dark:text-white">
                {product.name}
            </DataTableCell>

            <DataTableCell>
                {product.sku ?? "—"}
            </DataTableCell>

            <DataTableCell>
                {product.category.name}
            </DataTableCell>

            <DataTableCell>
                {product.inventory_items_count}
            </DataTableCell>

            <DataTableCell>
                {product.available_inventory_count}
            </DataTableCell>

            <DataTableCell>
                {formatCurrency(Number(product.price_per_day))}
            </DataTableCell>

            <DataTableCell>
                <StatusBadge
                    status={product.active ? "ACTIVE" : "INACTIVE"}
                    label={
                        product.active
                            ? labels.active
                            : labels.inactive
                    }
                />
            </DataTableCell>

            <DataTableCell className="sticky right-0 bg-white text-right dark:bg-slate-950">
                <ButtonLink
                    href={`/admin/products/${product.id}`}
                    variant="secondary"
                    size="sm"
                >
                    {labels.open}
                </ButtonLink>
            </DataTableCell>
        </DataTableRow>
    );
}