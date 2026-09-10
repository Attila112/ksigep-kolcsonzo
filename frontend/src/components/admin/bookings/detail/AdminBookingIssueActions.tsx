"use client";

import {
    useMemo,
    useState,
    useTransition,
} from "react";

import { useRouter } from "next/navigation";

import {
    issueBookingAction,
} from "@/core/admin/bookingActions";

import type {
    AdminBatteryItem,
} from "@/types/adminBattery";

import type {
    AdminBookingItem,
    AdminBookingStatus,
} from "@/types/adminBooking";

import type {
    AdminInventoryItem,
} from "@/types/adminInventory";

type AdminBookingIssueActionsProps = {
    bookingId: number;
    status: AdminBookingStatus;
    items: AdminBookingItem[];
    inventoryItems: AdminInventoryItem[];
    batteryItems: AdminBatteryItem[];

    labels: {
        title: string;
        description: string;

        product: string;
        machine: string;
        machineNumber: string;
        selectMachine: string;
        noMachineAvailable: string;

        accessories: string;
        batteries: string;
        chargers: string;

        required: string;
        selected: string;

        noBatteryRequired: string;
        noBatteryAvailable: string;
        noChargerAvailable: string;

        issue: string;
        issuing: string;

        machineRequired: string;
        duplicateMachine: string;
        accessoryRequirementInvalid: string;

        success: string;
        unknownError: string;
    };
};

type MachineSelectionMap =
    Record<string, number | null>;

type AccessorySelectionMap =
    Record<string, number[]>;

export function AdminBookingIssueActions({
    bookingId,
    status,
    items,
    inventoryItems,
    batteryItems,
    labels,
}: AdminBookingIssueActionsProps) {
    const router = useRouter();

    const [
        machineSelections,
        setMachineSelections,
    ] = useState<MachineSelectionMap>({});

    const [
        accessorySelections,
        setAccessorySelections,
    ] = useState<AccessorySelectionMap>({});

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

    const availableInventoryItems =
        useMemo(
            () =>
                inventoryItems.filter(
                    (item) =>
                        item.status ===
                        "AVAILABLE"
                ),
            [inventoryItems]
        );

    const availableBatteryItems =
        useMemo(
            () =>
                batteryItems.filter(
                    (item) =>
                        item.status ===
                        "AVAILABLE"
                ),
            [batteryItems]
        );

    if (status !== "CONFIRMED") {
        return null;
    }

    function getSlotKey(
        bookingItemId: number,
        index: number
    ): string {
        return `${bookingItemId}-${index}`;
    }

    function handleMachineChange(
        slotKey: string,
        inventoryItemId: number | null
    ) {
        setMachineSelections(
            (current) => ({
                ...current,
                [slotKey]:
                    inventoryItemId,
            })
        );

        /*
         * Másik gép kiválasztásakor az előző géphez
         * tartozó akkumulátor/töltő kijelöléseket töröljük.
         */
        setAccessorySelections(
            (current) => ({
                ...current,
                [slotKey]: [],
            })
        );

        setError(null);
        setSuccess(null);
    }

    function toggleAccessory(
        slotKey: string,
        batteryItemId: number
    ) {
        setAccessorySelections(
            (current) => {
                const selected =
                    current[slotKey] ?? [];

                const alreadySelected =
                    selected.includes(
                        batteryItemId
                    );

                return {
                    ...current,
                    [slotKey]:
                        alreadySelected
                            ? selected.filter(
                                  (id) =>
                                      id !==
                                      batteryItemId
                              )
                            : [
                                  ...selected,
                                  batteryItemId,
                              ],
                };
            }
        );

        setError(null);
        setSuccess(null);
    }

    function getSelectedMachineIds(): number[] {
        return Object.values(
            machineSelections
        ).filter(
            (
                value
            ): value is number =>
                value !== null
        );
    }

    function getAccessoryIdsUsedByOtherSlots(
        currentSlotKey: string
    ): number[] {
        return Object.entries(
            accessorySelections
        )
            .filter(
                ([slotKey]) =>
                    slotKey !==
                    currentSlotKey
            )
            .flatMap(
                ([, ids]) => ids
            );
    }

    function validate(): boolean {
        const requiredMachineCount =
            items.reduce(
                (total, item) =>
                    total +
                    item.quantity,
                0
            );

        const selectedMachineIds =
            getSelectedMachineIds();

        if (
            selectedMachineIds.length !==
            requiredMachineCount
        ) {
            setError(
                labels.machineRequired
            );

            return false;
        }

        if (
            new Set(
                selectedMachineIds
            ).size !==
            selectedMachineIds.length
        ) {
            setError(
                labels.duplicateMachine
            );

            return false;
        }

        for (const item of items) {
            for (
                let index = 0;
                index < item.quantity;
                index++
            ) {
                const slotKey =
                    getSlotKey(
                        item.id,
                        index
                    );

                const machineId =
                    machineSelections[
                        slotKey
                    ];

                if (!machineId) {
                    setError(
                        labels.machineRequired
                    );

                    return false;
                }

                const machine =
                    inventoryItems.find(
                        (inventoryItem) =>
                            inventoryItem.id ===
                            machineId
                    );

                if (!machine) {
                    setError(
                        labels.machineRequired
                    );

                    return false;
                }

                const requiredBatteries =
                    machine.product
                        .required_batteries;

                const requiredChargers =
                    machine.product
                        .required_chargers;

                if (
                    requiredBatteries ===
                        0 &&
                    requiredChargers ===
                        0
                ) {
                    continue;
                }

                const selectedAccessoryIds =
                    accessorySelections[
                        slotKey
                    ] ?? [];

                const selectedAccessories =
                    availableBatteryItems.filter(
                        (batteryItem) =>
                            selectedAccessoryIds.includes(
                                batteryItem.id
                            )
                    );

                const batteryCount =
                    selectedAccessories.filter(
                        (batteryItem) =>
                            batteryItem.type ===
                            "BATTERY"
                    ).length;

                const chargerCount =
                    selectedAccessories.filter(
                        (batteryItem) =>
                            batteryItem.type ===
                            "CHARGER"
                    ).length;

                if (
                    batteryCount !==
                        requiredBatteries ||
                    chargerCount !==
                        requiredChargers
                ) {
                    setError(
                        labels.accessoryRequirementInvalid
                    );

                    return false;
                }
            }
        }

        return true;
    }

    function handleIssue() {
        setError(null);
        setSuccess(null);

        if (!validate()) {
            return;
        }

        const inventoryItemIds =
            getSelectedMachineIds();

        const batteryAllocations =
            Object.entries(
                machineSelections
            )
                .filter(
                    (
                        entry
                    ): entry is [
                        string,
                        number,
                    ] =>
                        entry[1] !==
                        null
                )
                .map(
                    ([
                        slotKey,
                        inventoryItemId,
                    ]) => ({
                        inventory_item_id:
                            inventoryItemId,
                        battery_item_ids:
                            accessorySelections[
                                slotKey
                            ] ?? [],
                    })
                );

        startTransition(
            async () => {
                const result =
                    await issueBookingAction(
                        bookingId,
                        inventoryItemIds,
                        batteryAllocations
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

                setSuccess(
                    labels.success
                );

                router.refresh();
            }
        );
    }

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <div className="mb-6">
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

            <div className="space-y-6">
                {items.map(
                    (bookingItem) => {
                        const machines =
                            availableInventoryItems.filter(
                                (
                                    inventoryItem
                                ) =>
                                    inventoryItem
                                        .product
                                        .id ===
                                    bookingItem
                                        .product
                                        .id
                            );

                        return (
                            <div
                                key={
                                    bookingItem.id
                                }
                                className="rounded-lg border border-slate-200 p-4 dark:border-slate-800"
                            >
                                <div className="mb-4">
                                    <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        {
                                            labels.product
                                        }
                                    </p>

                                    <p className="mt-1 font-semibold text-slate-950 dark:text-white">
                                        {
                                            bookingItem
                                                .product
                                                .name
                                        }
                                    </p>
                                </div>

                                <div className="space-y-5">
                                    {Array.from(
                                        {
                                            length:
                                                bookingItem.quantity,
                                        },
                                        (
                                            _,
                                            index
                                        ) => {
                                            const slotKey =
                                                getSlotKey(
                                                    bookingItem.id,
                                                    index
                                                );

                                            const selectedMachineId =
                                                machineSelections[
                                                    slotKey
                                                ] ??
                                                null;

                                            const selectedMachine =
                                                inventoryItems.find(
                                                    (
                                                        inventoryItem
                                                    ) =>
                                                        inventoryItem.id ===
                                                        selectedMachineId
                                                );

                                            const otherSelectedMachineIds =
                                                Object.entries(
                                                    machineSelections
                                                )
                                                    .filter(
                                                        ([
                                                            key,
                                                        ]) =>
                                                            key !==
                                                            slotKey
                                                    )
                                                    .map(
                                                        ([
                                                            ,
                                                            id,
                                                        ]) =>
                                                            id
                                                    )
                                                    .filter(
                                                        (
                                                            id
                                                        ): id is number =>
                                                            id !==
                                                            null
                                                    );

                                            return (
                                                <div
                                                    key={
                                                        slotKey
                                                    }
                                                    className="rounded-lg bg-slate-50 p-4 dark:bg-slate-900/50"
                                                >
                                                    <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                                        {
                                                            labels.machine
                                                        }{" "}
                                                        {index +
                                                            1}
                                                    </label>

                                                    <select
                                                        value={
                                                            selectedMachineId ??
                                                            ""
                                                        }
                                                        onChange={(
                                                            event
                                                        ) =>
                                                            handleMachineChange(
                                                                slotKey,
                                                                event
                                                                    .target
                                                                    .value
                                                                    ? Number(
                                                                          event
                                                                              .target
                                                                              .value
                                                                      )
                                                                    : null
                                                            )
                                                        }
                                                        disabled={
                                                            isPending
                                                        }
                                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition focus:border-slate-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                                    >
                                                        <option value="">
                                                            {
                                                                labels.selectMachine
                                                            }
                                                        </option>

                                                        {machines.map(
                                                            (
                                                                machine
                                                            ) => (
                                                                <option
                                                                    key={
                                                                        machine.id
                                                                    }
                                                                    value={
                                                                        machine.id
                                                                    }
                                                                    disabled={otherSelectedMachineIds.includes(
                                                                        machine.id
                                                                    )}
                                                                >
                                                                    {
                                                                        machine.inventory_code
                                                                    }
                                                                    {machine.serial_number
                                                                        ? ` – ${machine.serial_number}`
                                                                        : ""}
                                                                </option>
                                                            )
                                                        )}
                                                    </select>

                                                    {machines.length ===
                                                        0 && (
                                                        <p className="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                                            {
                                                                labels.noMachineAvailable
                                                            }
                                                        </p>
                                                    )}

                                                    {selectedMachine && (
                                                        <AccessorySelector
                                                            slotKey={
                                                                slotKey
                                                            }
                                                            machine={
                                                                selectedMachine
                                                            }
                                                            batteryItems={
                                                                availableBatteryItems
                                                            }
                                                            selectedIds={
                                                                accessorySelections[
                                                                    slotKey
                                                                ] ??
                                                                []
                                                            }
                                                            usedByOtherSlots={getAccessoryIdsUsedByOtherSlots(
                                                                slotKey
                                                            )}
                                                            disabled={
                                                                isPending
                                                            }
                                                            labels={
                                                                labels
                                                            }
                                                            onToggle={
                                                                toggleAccessory
                                                            }
                                                        />
                                                    )}
                                                </div>
                                            );
                                        }
                                    )}
                                </div>
                            </div>
                        );
                    }
                )}
            </div>

            <div className="mt-6 flex justify-end">
                <button
                    type="button"
                    onClick={
                        handleIssue
                    }
                    disabled={
                        isPending
                    }
                    className="inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {isPending
                        ? labels.issuing
                        : labels.issue}
                </button>
            </div>
        </section>
    );
}

type AccessorySelectorProps = {
    slotKey: string;
    machine: AdminInventoryItem;
    batteryItems: AdminBatteryItem[];

    selectedIds: number[];
    usedByOtherSlots: number[];

    disabled: boolean;

    labels: AdminBookingIssueActionsProps["labels"];

    onToggle: (
        slotKey: string,
        batteryItemId: number
    ) => void;
};

function AccessorySelector({
    slotKey,
    machine,
    batteryItems,
    selectedIds,
    usedByOtherSlots,
    disabled,
    labels,
    onToggle,
}: AccessorySelectorProps) {
    const batterySystem =
        machine.product.battery_system;

    const requiredBatteries =
        machine.product.required_batteries;

    const requiredChargers =
        machine.product.required_chargers;

    if (
        !batterySystem ||
        (
            requiredBatteries === 0 &&
            requiredChargers === 0
        )
    ) {
        return (
            <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">
                {
                    labels.noBatteryRequired
                }
            </p>
        );
    }

    const compatibleItems =
        batteryItems.filter(
            (batteryItem) =>
                batteryItem.battery_system_id ===
                batterySystem.id
        );

    const batteries =
        compatibleItems.filter(
            (batteryItem) =>
                batteryItem.type ===
                "BATTERY"
        );

    const chargers =
        compatibleItems.filter(
            (batteryItem) =>
                batteryItem.type ===
                "CHARGER"
        );

    const selectedBatteryCount =
        batteries.filter(
            (batteryItem) =>
                selectedIds.includes(
                    batteryItem.id
                )
        ).length;

    const selectedChargerCount =
        chargers.filter(
            (batteryItem) =>
                selectedIds.includes(
                    batteryItem.id
                )
        ).length;

    return (
        <div className="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800">
            <h3 className="text-sm font-semibold text-slate-950 dark:text-white">
                {
                    labels.accessories
                }
            </h3>

            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {batterySystem.manufacturer}{" "}
                {batterySystem.name} (
                {batterySystem.voltage} V)
            </p>

            <AccessoryGroup
                title={labels.batteries}
                required={
                    requiredBatteries
                }
                selected={
                    selectedBatteryCount
                }
                items={batteries}
                selectedIds={
                    selectedIds
                }
                usedByOtherSlots={
                    usedByOtherSlots
                }
                disabled={
                    disabled
                }
                emptyLabel={
                    labels.noBatteryAvailable
                }
                requiredLabel={
                    labels.required
                }
                selectedLabel={
                    labels.selected
                }
                slotKey={
                    slotKey
                }
                onToggle={
                    onToggle
                }
            />

            <AccessoryGroup
                title={labels.chargers}
                required={
                    requiredChargers
                }
                selected={
                    selectedChargerCount
                }
                items={chargers}
                selectedIds={
                    selectedIds
                }
                usedByOtherSlots={
                    usedByOtherSlots
                }
                disabled={
                    disabled
                }
                emptyLabel={
                    labels.noChargerAvailable
                }
                requiredLabel={
                    labels.required
                }
                selectedLabel={
                    labels.selected
                }
                slotKey={
                    slotKey
                }
                onToggle={
                    onToggle
                }
            />
        </div>
    );
}

type AccessoryGroupProps = {
    title: string;
    required: number;
    selected: number;

    items: AdminBatteryItem[];

    selectedIds: number[];
    usedByOtherSlots: number[];

    disabled: boolean;

    emptyLabel: string;
    requiredLabel: string;
    selectedLabel: string;

    slotKey: string;

    onToggle: (
        slotKey: string,
        batteryItemId: number
    ) => void;
};

function AccessoryGroup({
    title,
    required,
    selected,
    items,
    selectedIds,
    usedByOtherSlots,
    disabled,
    emptyLabel,
    requiredLabel,
    selectedLabel,
    slotKey,
    onToggle,
}: AccessoryGroupProps) {
    if (required === 0) {
        return null;
    }

    return (
        <div className="mt-4">
            <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                <p className="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {title}
                </p>

                <p className="text-xs text-slate-500 dark:text-slate-400">
                    {requiredLabel}:{" "}
                    {required} ·{" "}
                    {selectedLabel}:{" "}
                    {selected}
                </p>
            </div>

            {items.length === 0 ? (
                <p className="text-sm text-amber-700 dark:text-amber-300">
                    {emptyLabel}
                </p>
            ) : (
                <div className="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    {items.map(
                        (item) => {
                            const checked =
                                selectedIds.includes(
                                    item.id
                                );

                            const usedElsewhere =
                                usedByOtherSlots.includes(
                                    item.id
                                );

                            return (
                                <label
                                    key={
                                        item.id
                                    }
                                    className="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <input
                                        type="checkbox"
                                        checked={
                                            checked
                                        }
                                        disabled={
                                            disabled ||
                                            usedElsewhere
                                        }
                                        onChange={() =>
                                            onToggle(
                                                slotKey,
                                                item.id
                                            )
                                        }
                                    />

                                    <span className="min-w-0">
                                        <span className="block font-medium text-slate-950 dark:text-white">
                                            {
                                                item.inventory_code
                                            }
                                        </span>

                                        {item.serial_number && (
                                            <span className="block truncate text-xs text-slate-500 dark:text-slate-400">
                                                {
                                                    item.serial_number
                                                }
                                            </span>
                                        )}
                                    </span>
                                </label>
                            );
                        }
                    )}
                </div>
            )}
        </div>
    );
}