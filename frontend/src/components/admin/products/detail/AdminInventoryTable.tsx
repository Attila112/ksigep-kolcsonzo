import { AdminInventoryStatusBadge } from "@/components/admin/products/AdminInventoryStatusBadge";
import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableHead,
    DataTableHeader,
    DataTableRow,
} from "@/components/ui/DataTable";
import { Heading } from "@/components/ui/Heading";
import type { AdminProductDetail } from "@/types/adminProduct";

type AdminInventoryTableProps = {
    product: AdminProductDetail;
    labels: {
        title: string;
        inventoryCode: string;
        serialNumber: string;
        status: string;
        adminNote: string;
        noInventory: string;
        noSerialNumber: string;
        noAdminNote: string;
    };
};

export function AdminInventoryTable({
    product,
    labels,
}: AdminInventoryTableProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <div className="mb-5">
                <Heading level={2} size="md">
                    {labels.title}
                </Heading>
            </div>

            {product.inventory_items.length > 0 ? (
                <DataTable className="min-w-180">
                    <DataTableHeader>
                        <DataTableRow>
                            <DataTableHead>
                                {labels.inventoryCode}
                            </DataTableHead>

                            <DataTableHead>
                                {labels.serialNumber}
                            </DataTableHead>

                            <DataTableHead>
                                {labels.status}
                            </DataTableHead>

                            <DataTableHead>
                                {labels.adminNote}
                            </DataTableHead>
                        </DataTableRow>
                    </DataTableHeader>

                    <DataTableBody>
                        {product.inventory_items.map((item) => (
                            <DataTableRow key={item.id}>
                                <DataTableCell className="font-medium text-slate-950 dark:text-white">
                                    {item.inventory_code}
                                </DataTableCell>

                                <DataTableCell>
                                    {item.serial_number ??
                                        labels.noSerialNumber}
                                </DataTableCell>

                                <DataTableCell>
                                    <AdminInventoryStatusBadge
                                        status={item.status}
                                    />
                                </DataTableCell>

                                <DataTableCell>
                                    {item.admin_note ??
                                        labels.noAdminNote}
                                </DataTableCell>
                            </DataTableRow>
                        ))}
                    </DataTableBody>
                </DataTable>
            ) : (
                <p className="text-sm text-slate-500 dark:text-slate-400">
                    {labels.noInventory}
                </p>
            )}
        </section>
    );
}