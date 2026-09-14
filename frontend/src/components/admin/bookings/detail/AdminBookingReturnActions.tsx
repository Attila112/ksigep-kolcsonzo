"use client";

import {
    useMemo,
    useState,
    useTransition,
} from "react";

import { useRouter } from "next/navigation";

import {
    returnBookingItemsAction,
} from "@/core/admin/bookingActions";

import type {
    AdminBookingItem,
    AdminBookingStatus,
} from "@/types/adminBooking";

type AdminBookingReturnActionsProps = {
    bookingId: number;
    status: AdminBookingStatus;
    items: AdminBookingItem[];

    labels: {
        title: string;
        description: string;

        product: string;
        machine: string;
        serialNumber: string;
        accessories: string;

        battery: string;
        charger: string;

        noSerialNumber: string;

        selectAtLeastOne: string;

        returnSelected: string;
        returning: string;

        success: string;
        unknownError: string;
    };
};

export function AdminBookingReturnActions({
    bookingId,
    status,
    items,
    labels,
}: AdminBookingReturnActionsProps) {
    const router = useRouter();

    const [
        selectedInventoryItemIds,
        setSelectedInventoryItemIds,
    ] = useState<number[]>([]);

    const [
        error,
        setError,
    ] = useState<string | null>(null);

    const [
        success,
        setSuccess,
    ] = useState<string | null>(null);

    const [
        isPending,
        startTransition,
    ] = useTransition();

    const activeAllocations =
        useMemo(
            () =>
                items.flatMap(
                    (item) =>
                        item.allocations
                            .filter(
                                (allocation) =>
                                    allocation.returned_at ===
                                    null
                            )
                            .map(
                                (allocation) => ({
                                    product:
                                        item.product,
                                    allocation,
                                })
                            )
                ),
            [items]
        );

    if (status !== "ACTIVE") {
        return null;
    }

    function toggleInventoryItem(
        inventoryItemId: number
    ) {
        setSelectedInventoryItemIds(
            (current) =>
                current.includes(
                    inventoryItemId
                )
                    ? current.filter(
                          (id) =>
                              id !==
                              inventoryItemId
                      )
                    : [
                          ...current,
                          inventoryItemId,
                      ]
        );

        setError(null);
        setSuccess(null);
    }

    function handleReturn() {
        setError(null);
        setSuccess(null);

        if (
            selectedInventoryItemIds.length ===
            0
        ) {
            setError(
                labels.selectAtLeastOne
            );

            return;
        }

        startTransition(
            async () => {
                const result =
                    await returnBookingItemsAction(
                        bookingId,
                        selectedInventoryItemIds
                    );

                if (!result.success) {
                    setError(
                        result.message ===
                            "UNKNOWN_ERROR"
                            ? labels.unknownError
                            : result.message
                    );

                    return;
                }

                setSelectedInventoryItemIds(
                    []
                );

                setSuccess(
                    labels.success
                );

                router.refresh();
            }
        );
    }

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <div className="mb-5">
                <h2 className="text-base font-semibold text-slate-950 dark:text-white">
                    {labels.title}
                </h2>

                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {
                        labels.description
                    }
                </p>
            </div>

            {error && (
                <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                    {error}
                </div>
            )}

            {success && (
                <div className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {success}
                </div>
            )}

            <div className="space-y-3">
                {activeAllocations.map(
                    ({
                        product,
                        allocation,
                    }) => {
                        const inventoryItem =
                            allocation.inventory_item;

                        const checked =
                            selectedInventoryItemIds.includes(
                                inventoryItem.id
                            );

                        return (
                            <label
                                key={
                                    allocation.id
                                }
                                className="block cursor-pointer rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900/50"
                            >
                                <div className="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        checked={
                                            checked
                                        }
                                        disabled={
                                            isPending
                                        }
                                        onChange={() =>
                                            toggleInventoryItem(
                                                inventoryItem.id
                                            )
                                        }
                                        className="mt-1"
                                    />

                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                    {
                                                        labels.product
                                                    }
                                                </p>

                                                <p className="mt-1 font-semibold text-slate-950 dark:text-white">
                                                    {
                                                        product.name
                                                    }
                                                </p>
                                            </div>

                                            <div className="sm:text-right">
                                                <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                    {
                                                        labels.machine
                                                    }
                                                </p>

                                                <p className="mt-1 text-sm font-medium text-slate-950 dark:text-white">
                                                    {
                                                        inventoryItem.inventory_code
                                                    }
                                                </p>
                                            </div>
                                        </div>

                                        <div className="mt-3">
                                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                                {
                                                    labels.serialNumber
                                                }
                                                :{" "}
                                                {inventoryItem.serial_number ??
                                                    labels.noSerialNumber}
                                            </p>
                                        </div>

                                        {allocation
                                            .battery_item_allocations
                                            .length >
                                            0 && (
                                            <div className="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800">
                                                <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                    {
                                                        labels.accessories
                                                    }
                                                </p>

                                                <div className="mt-2 flex flex-wrap gap-2">
                                                    {allocation.battery_item_allocations.map(
                                                        (
                                                            batteryAllocation
                                                        ) => {
                                                            const batteryItem =
                                                                batteryAllocation.battery_item;

                                                            return (
                                                                <span
                                                                    key={
                                                                        batteryAllocation.id
                                                                    }
                                                                    className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                                                >
                                                                    {
                                                                        batteryItem.inventory_code
                                                                    }
                                                                    {" · "}
                                                                    {batteryItem.type ===
                                                                    "BATTERY"
                                                                        ? labels.battery
                                                                        : labels.charger}
                                                                </span>
                                                            );
                                                        }
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </label>
                        );
                    }
                )}
            </div>

            <div className="mt-6 flex justify-end">
                <button
                    type="button"
                    onClick={
                        handleReturn
                    }
                    disabled={
                        isPending
                    }
                    className="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {isPending
                        ? labels.returning
                        : labels.returnSelected}
                </button>
            </div>
        </section>
    );
}