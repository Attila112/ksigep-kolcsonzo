import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableHead,
    DataTableHeader,
    DataTableRow,
} from "@/components/ui/DataTable";
import { StatusBadge } from "@/components/ui/StatusBadge";
import { ButtonLink } from "@/components/ui/ButtonLink";

import type {
    AdminInventoryItem,
    AdminInventoryStatus,
} from "@/types/adminInventory";

type AdminInventoryTableProps = {
    items: AdminInventoryItem[];

    labels: {
        columns: {
            inventoryCode: string;
            product: string;
            sku: string;
            category: string;
            serialNumber: string;
            status: string;
            adminNote: string;
            action: string;
        };

        statuses: Record<
            AdminInventoryStatus,
            string
        >;

        open: string;
        noSerialNumber: string;
        noAdminNote: string;
        empty: string;
    };
};

export function AdminInventoryTable({
    items,
    labels,
}: AdminInventoryTableProps) {
    if (items.length === 0) {
        return (
            <div className="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                {labels.empty}
            </div>
        );
    }

    return (
        <DataTable className="min-w-312.5">
            <DataTableHeader>
                <DataTableRow>
                    <DataTableHead>
                        {labels.columns.inventoryCode}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.product}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.sku}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.category}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.serialNumber}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.status}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.adminNote}
                    </DataTableHead>

                    <DataTableHead className="sticky right-0 bg-slate-50 text-right dark:bg-slate-900">
                        {labels.columns.action}
                    </DataTableHead>
                </DataTableRow>
            </DataTableHeader>

            <DataTableBody>
                {items.map((item) => (
                    <DataTableRow key={item.id}>
                        <DataTableCell className="font-medium text-slate-950 dark:text-white">
                            {item.inventory_code}
                        </DataTableCell>

                        <DataTableCell>
                            {item.product.name}
                        </DataTableCell>

                        <DataTableCell>
                            {item.product.sku ?? "—"}
                        </DataTableCell>

                        <DataTableCell>
                            {item.product.category.name}
                        </DataTableCell>

                        <DataTableCell>
                            {item.serial_number ??
                                labels.noSerialNumber}
                        </DataTableCell>

                        <DataTableCell>
                            <StatusBadge
                                status={item.status}
                                label={
                                    labels.statuses[
                                        item.status
                                    ]
                                }
                            />
                        </DataTableCell>

                        <DataTableCell>
                            {item.admin_note ??
                                labels.noAdminNote}
                        </DataTableCell>

                        <DataTableCell className="sticky right-0 bg-white text-right dark:bg-slate-950">
                            <ButtonLink
                                href={`/admin/inventory/${item.id}`}
                                variant="secondary"
                                size="sm"
                            >
                                {labels.open}
                            </ButtonLink>
                        </DataTableCell>
                    </DataTableRow>
                ))}
            </DataTableBody>
        </DataTable>
    );
}