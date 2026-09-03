"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useTranslations } from "next-intl";

import {
    updateAdminInventoryStatus,
} from "@/core/admin/inventoryActions";

import type {
    AdminInventoryDetail,
    AdminInventoryStatus,
} from "@/types/adminInventory";

type AdminInventoryStatusFormProps = {
    item: AdminInventoryDetail;
};

const statuses: AdminInventoryStatus[] = [
    "AVAILABLE",
    "INSPECTION",
    "MAINTENANCE",
    "DAMAGED",
    "INACTIVE",
];

export function AdminInventoryStatusForm({
    item,
}: AdminInventoryStatusFormProps) {
    const t = useTranslations("Admin");
    const router = useRouter();

    const [status, setStatus] =
        useState<AdminInventoryStatus>(
            item.status
        );

    const [adminNote, setAdminNote] =
        useState("");

    const [saving, setSaving] =
        useState(false);

    const [error, setError] =
        useState<string | null>(null);

    const [success, setSuccess] =
        useState(false);
    const [adminNoteError, setAdminNoteError] =
        useState<string | null>(null);

    async function handleSubmit(
        event: React.FormEvent<HTMLFormElement>
    ) {
        event.preventDefault();

        setSaving(true);
        setError(null);
        setSuccess(false);
        setAdminNoteError(null);
        const requiresAdminNote =
            status !== "AVAILABLE";

        if (
            requiresAdminNote &&
            adminNote.trim() === ""
        ) {
            setAdminNoteError(
                t(
                    "inventory.statusChange.adminNoteRequired"
                )
            );

            setSaving(false);

            return;
        }


        const result =
            await updateAdminInventoryStatus(
                item.id,
                {
                    status,
                    admin_note:
                        adminNote.trim() === ""
                            ? null
                            : adminNote.trim(),
                }
            );

        if (!result.success) {
            if (result.errors?.admin_note?.[0]) {
                setAdminNoteError(
                    result.errors.admin_note[0]
                );
            } else {
                setError(
                    result.message ??
                    t(
                        "inventory.statusChange.saveError"
                    )
                );
            }

            return;
        }

        setAdminNote("");
        setSuccess(true);

        router.refresh();

        setSaving(false);
    }

    const statusChanged =
        status !== item.status;

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
                {t(
                    "inventory.statusChange.title"
                )}
            </h2>

            <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {t(
                    "inventory.statusChange.description"
                )}
            </p>

            <form
                onSubmit={handleSubmit}
                className="mt-5 space-y-5"
            >
                <div>
                    <label
                        htmlFor="inventory-status"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t(
                            "inventory.statusChange.status"
                        )}
                    </label>

                    <select
                        id="inventory-status"
                        value={status}
                        onChange={(event) =>
                            setStatus(
                                event.target
                                    .value as AdminInventoryStatus
                            )
                        }
                        disabled={saving}
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                        {statuses.map(
                            (statusOption) => (
                                <option
                                    key={
                                        statusOption
                                    }
                                    value={
                                        statusOption
                                    }
                                >
                                    {t(
                                        `inventoryStatus.${statusOption.toLowerCase()}`
                                    )}
                                </option>
                            )
                        )}
                    </select>
                </div>

                <div>
                    <label
                        htmlFor="inventory-admin-note"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t(
                            "inventory.statusChange.adminNote"
                        )}

                        {status !== "AVAILABLE" && (
                            <span
                                className="ml-1 text-red-600 dark:text-red-400"
                                aria-hidden="true"
                            >
                                *
                            </span>
                        )}
                    </label>

                    <textarea
                        id="inventory-admin-note"
                        value={adminNote}
                        onChange={(event) =>
                            setAdminNote(
                                event.target.value
                            )
                        }
                        disabled={saving}
                        rows={4}
                        placeholder={t(
                            "inventory.statusChange.adminNotePlaceholder"
                        )}
                        className="w-full resize-y rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>

                {error && (
                    <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                        {error}
                    </div>
                )}

                {success && (
                    <div className="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900 dark:bg-green-950 dark:text-green-300">
                        {t(
                            "inventory.statusChange.saveSuccess"
                        )}
                    </div>
                )}
                {adminNoteError && (
                    <p className="mt-1.5 text-sm text-red-600 dark:text-red-400">
                        {adminNoteError}
                    </p>
                )}

                <div className="flex justify-end">
                    <button
                        type="submit"
                        disabled={
                            saving ||
                            !statusChanged
                        }
                        className="w-full rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 sm:w-auto"
                    >
                        {saving
                            ? t(
                                "inventory.statusChange.saving"
                            )
                            : t(
                                "inventory.statusChange.save"
                            )}
                    </button>
                </div>
            </form>
        </section>
    );
}