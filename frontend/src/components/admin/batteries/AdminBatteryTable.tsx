import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableHead,
    DataTableHeader,
    DataTableRow,
} from "@/components/ui/DataTable";

import { ButtonLink } from "@/components/ui/ButtonLink";
import { StatusBadge } from "@/components/ui/StatusBadge";

import type {
    AdminBatteryItem,
    AdminBatteryStatus,
    AdminBatteryItemType,
} from "@/types/adminBattery";

type AdminBatteryTableProps = {
    items: AdminBatteryItem[];

    labels: {
        columns: {
            inventoryCode: string;
            type: string;
            system: string;
            manufacturer: string;
            voltage: string;
            serialNumber: string;
            status: string;
            action: string;
        };

        types: Record<
            AdminBatteryItemType,
            string
        >;

        statuses: Record<
            AdminBatteryStatus,
            string
        >;

        noSerialNumber: string;
        open: string;
        empty: string;
    };
};

export function AdminBatteryTable({
    items,
    labels,
}: AdminBatteryTableProps) {
    if (items.length === 0) {
        return (
            <div className="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                {labels.empty}
            </div>
        );
    }

    return (
        <DataTable className="min-w-287.5">
            <DataTableHeader>
                <DataTableRow>
                    <DataTableHead>
                        {labels.columns.inventoryCode}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.type}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.system}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.manufacturer}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.voltage}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.serialNumber}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.status}
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
                            {labels.types[item.type]}
                        </DataTableCell>

                        <DataTableCell>
                            {item.battery_system.name}
                        </DataTableCell>

                        <DataTableCell>
                            {item.battery_system.manufacturer}
                        </DataTableCell>

                        <DataTableCell>
                            {item.battery_system.voltage} V
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

                        <DataTableCell className="sticky right-0 bg-white text-right dark:bg-slate-950">
                            <ButtonLink
                                href={`/admin/batteries/${item.id}`}
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