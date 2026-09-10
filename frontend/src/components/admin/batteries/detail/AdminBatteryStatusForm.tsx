"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";

import { updateBatteryStatusAction } from "@/core/admin/batteryActions";

import type {
    AdminBatteryStatus,
} from "@/types/adminBattery";

type ManualBatteryStatus = Exclude<
    AdminBatteryStatus,
    "RENTED"
>;

type AdminBatteryStatusFormProps = {
    batteryItemId: number;
    currentStatus: AdminBatteryStatus;

    labels: {
        title: string;
        description: string;
        status: string;
        adminNote: string;
        adminNoteRequired: string;
        save: string;
        saving: string;
        success: string;
        error: string;
        rentedLocked: string;

        statuses: Record<
            ManualBatteryStatus,
            string
        >;
    };
};

const MANUAL_STATUSES: ManualBatteryStatus[] = [
    "AVAILABLE",
    "INSPECTION",
    "MAINTENANCE",
    "DAMAGED",
    "INACTIVE",
];

export function AdminBatteryStatusForm({
    batteryItemId,
    currentStatus,
    labels,
}: AdminBatteryStatusFormProps) {
    const router = useRouter();

    const [status, setStatus] =
        useState<ManualBatteryStatus>(
            currentStatus === "RENTED"
                ? "AVAILABLE"
                : currentStatus
        );

    const [adminNote, setAdminNote] =
        useState("");

    const [message, setMessage] =
        useState<string | null>(null);

    const [error, setError] =
        useState<string | null>(null);

    const [isPending, startTransition] =
        useTransition();

    const noteRequired =
        status !== "AVAILABLE";

    const rented =
        currentStatus === "RENTED";

    function handleSubmit(
        event: React.FormEvent<HTMLFormElement>
    ) {
        event.preventDefault();

        setMessage(null);
        setError(null);

        if (
            noteRequired &&
            !adminNote.trim()
        ) {
            setError(
                labels.adminNoteRequired
            );

            return;
        }

        startTransition(async () => {
            const result =
                await updateBatteryStatusAction(
                    batteryItemId,
                    status,
                    adminNote.trim() || null
                );

            if (!result.success) {
                const validationMessage =
                    result.errors
                        ?.admin_note?.[0] ??
                    result.errors
                        ?.status?.[0];

                setError(
                    validationMessage ??
                        labels.error
                );

                return;
            }

            setAdminNote("");
            setMessage(labels.success);

            router.refresh();
        });
    }

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
                {labels.title}
            </h2>

            <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {labels.description}
            </p>

            {rented ? (
                <p className="mt-5 rounded-md border border-slate-200 p-3 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                    {labels.rentedLocked}
                </p>
            ) : (
                <form
                    onSubmit={handleSubmit}
                    className="mt-5 space-y-4"
                >
                    <div>
                        <label
                            htmlFor="battery-status"
                            className="block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            {labels.status}
                        </label>

                        <select
                            id="battery-status"
                            value={status}
                            disabled={isPending}
                            onChange={(event) =>
                                setStatus(
                                    event.target
                                        .value as ManualBatteryStatus
                                )
                            }
                            className="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition focus:border-slate-500 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        >
                            {MANUAL_STATUSES.map(
                                (itemStatus) => (
                                    <option
                                        key={
                                            itemStatus
                                        }
                                        value={
                                            itemStatus
                                        }
                                    >
                                        {
                                            labels
                                                .statuses[
                                                itemStatus
                                            ]
                                        }
                                    </option>
                                )
                            )}
                        </select>
                    </div>

                    <div>
                        <label
                            htmlFor="battery-admin-note"
                            className="block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            {labels.adminNote}

                            {noteRequired && (
                                <span
                                    className="ml-1 text-red-600"
                                    aria-hidden="true"
                                >
                                    *
                                </span>
                            )}
                        </label>

                        <textarea
                            id="battery-admin-note"
                            rows={4}
                            value={adminNote}
                            disabled={isPending}
                            required={noteRequired}
                            onChange={(event) =>
                                setAdminNote(
                                    event.target.value
                                )
                            }
                            className="mt-1 block w-full resize-y rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition focus:border-slate-500 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>

                    {error && (
                        <p className="text-sm text-red-600 dark:text-red-400">
                            {error}
                        </p>
                    )}

                    {message && (
                        <p className="text-sm text-green-700 dark:text-green-400">
                            {message}
                        </p>
                    )}

                    <button
                        type="submit"
                        disabled={isPending}
                        className="inline-flex min-h-10 items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200"
                    >
                        {isPending
                            ? labels.saving
                            : labels.save}
                    </button>
                </form>
            )}
        </section>
    );
}