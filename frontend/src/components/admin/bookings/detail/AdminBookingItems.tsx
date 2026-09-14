import type {
    AdminBookingItem,
} from "@/types/adminBooking";

import type {
    AdminBatteryStatus,
} from "@/types/adminBattery";
import {
    formatDateTime,
} from "@/utils/formatDate";
import Link from "next/link";

type AdminBookingItemsProps = {
    items: AdminBookingItem[];
    locale: string;
    labels: {
        title: string;
        quantity: string;
        rentalDays: string;
        inventoryItem: string;
        serialNumber: string;
        assignedAt: string;
        returnedAt: string;
        accessories: string;
        battery: string;
        charger: string;
        batterySystem: string;
        noSerialNumber: string;
        notReturned: string;
        noAllocation: string;
        statuses: Record<
            AdminBatteryStatus | string,
            string
        >;
    };
};

export function AdminBookingItems({
    items,
    locale,
    labels,
}: AdminBookingItemsProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
                {labels.title}
            </h2>

            <div className="mt-5 space-y-6">
                {items.map((item) => (
                    <div
                        key={item.id}
                        className="rounded-lg border border-slate-200 p-4 dark:border-slate-800"
                    >
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <h3 className="font-semibold text-slate-950 dark:text-white">
                                {item.product.name}
                            </h3>

                            <div className="text-sm text-slate-500 dark:text-slate-400">
                                {labels.quantity}:{" "}
                                {item.quantity}
                                {" · "}
                                {labels.rentalDays}:{" "}
                                {item.rental_days}
                            </div>
                        </div>

                        {item.allocations.length ===
                            0 ? (
                            <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">
                                {
                                    labels.noAllocation
                                }
                            </p>
                        ) : (
                            <div className="mt-4 space-y-4">
                                {item.allocations.map(
                                    (allocation) => (
                                        <div
                                            key={
                                                allocation.id
                                            }
                                            className="rounded-lg bg-slate-50 p-4 dark:bg-slate-900"
                                        >
                                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                                <Info
                                                    label={
                                                        labels.inventoryItem
                                                    }
                                                    value={
                                                        allocation.inventory_item.inventory_code
                                                    }
                                                    href={`/${locale}/admin/inventory/${allocation.inventory_item.id}`}
                                                />

                                                <Info
                                                    label={
                                                        labels.serialNumber
                                                    }
                                                    value={
                                                        allocation
                                                            .inventory_item
                                                            .serial_number ??
                                                        labels.noSerialNumber
                                                    }
                                                />

                                                <Info
                                                    label={
                                                        labels.assignedAt
                                                    }
                                                    value={
                                                        formatDateTime(allocation.assigned_at)
                                                    }
                                                />

                                                <Info
                                                    label={
                                                        labels.returnedAt
                                                    }
                                                    value={
                                                        allocation.returned_at
                                                            ? formatDateTime(
                                                                allocation.returned_at
                                                            )
                                                            : labels.notReturned
                                                    }
                                                />
                                            </div>

                                            {allocation
                                                .battery_item_allocations
                                                .length >
                                                0 && (
                                                    <div className="mt-5 border-t border-slate-200 pt-4 dark:border-slate-800">
                                                        <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                            {
                                                                labels.accessories
                                                            }
                                                        </p>

                                                        <div className="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                                            {allocation.battery_item_allocations.map(
                                                                (
                                                                    batteryAllocation
                                                                ) => {
                                                                    const batteryItem =
                                                                        batteryAllocation.battery_item;

                                                                    return (
                                                                        <div
                                                                            key={
                                                                                batteryAllocation.id
                                                                            }
                                                                            className="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-950"
                                                                        >
                                                                            <div className="flex items-start justify-between gap-3">
                                                                                <div>
                                                                                    <Link
                                                                                        href={`/${locale}/admin/batteries/${batteryItem.id}`}
                                                                                        className="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                                                                    >
                                                                                        {batteryItem.inventory_code}
                                                                                    </Link>

                                                                                    <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                                                        {batteryItem.type ===
                                                                                            "BATTERY"
                                                                                            ? labels.battery
                                                                                            : labels.charger}
                                                                                    </p>
                                                                                </div>

                                                                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                                                                    {
                                                                                        labels.statuses[
                                                                                        batteryItem.status
                                                                                        ]
                                                                                    }
                                                                                </span>
                                                                            </div>

                                                                            <div className="mt-3 text-xs text-slate-500 dark:text-slate-400">
                                                                                {
                                                                                    labels.batterySystem
                                                                                }
                                                                                :{" "}
                                                                                {
                                                                                    batteryItem
                                                                                        .battery_system
                                                                                        .name
                                                                                }{" "}
                                                                                (
                                                                                {
                                                                                    batteryItem
                                                                                        .battery_system
                                                                                        .voltage
                                                                                }{" "}
                                                                                V)
                                                                            </div>
                                                                        </div>
                                                                    );
                                                                }
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                        </div>
                                    )
                                )}
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </section>
    );
}

type InfoProps = {
    label: string;
    value: string;
    href?: string;
};

function Info({
    label,
    value,
    href,
}: InfoProps) {
    return (
        <div>
            <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                {label}
            </p>

            {href ? (
                <Link
                    href={href}
                    className="mt-1 inline-block wrap-break-word text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                >
                    {value}
                </Link>
            ) : (
                <p className="mt-1 wrap-break-word text-sm font-medium text-slate-900 dark:text-slate-100">
                    {value}
                </p>
            )}
        </div>
    );
}