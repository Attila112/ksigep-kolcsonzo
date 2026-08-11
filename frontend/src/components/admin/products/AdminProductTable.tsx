import type { AdminProduct } from "@/types/adminProduct";

import {
    DataTable,
    DataTableBody,
    DataTableHead,
    DataTableHeader,
    DataTableRow,
} from "@/components/ui/DataTable";

import { AdminProductRow } from "./AdminProductRow";

type AdminProductTableProps = {
    products: AdminProduct[];
    labels: {
        columns: {
            image: string;
            name: string;
            sku: string;
            category: string;
            stock: string;
            available: string;
            price: string;
            status: string;
            action: string;
        };
        active: string;
        inactive: string;
        open: string;
    };
};

export function AdminProductTable({
    products,
    labels,
}: AdminProductTableProps) {
    return (
        <DataTable>
            <DataTableHeader>
                <DataTableRow>
                    <DataTableHead>{labels.columns.image}</DataTableHead>
                    <DataTableHead>{labels.columns.name}</DataTableHead>
                    <DataTableHead>{labels.columns.sku}</DataTableHead>
                    <DataTableHead>{labels.columns.category}</DataTableHead>
                    <DataTableHead>{labels.columns.stock}</DataTableHead>
                    <DataTableHead>{labels.columns.available}</DataTableHead>
                    <DataTableHead>{labels.columns.price}</DataTableHead>
                    <DataTableHead>{labels.columns.status}</DataTableHead>
                    <DataTableHead className="text-right">
                        {labels.columns.action}
                    </DataTableHead>
                </DataTableRow>
            </DataTableHeader>

            <DataTableBody>
                {products.map((product) => (
                    <AdminProductRow
                        key={product.id}
                        product={product}
                        labels={{
                            active: labels.active,
                            inactive: labels.inactive,
                            open: labels.open,
                        }}
                    />
                ))}
            </DataTableBody>
        </DataTable>
    );
}