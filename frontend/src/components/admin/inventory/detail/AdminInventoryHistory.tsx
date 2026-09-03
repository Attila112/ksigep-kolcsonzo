import { Heading } from "@/components/ui/Heading";
import { StatusBadge } from "@/components/ui/StatusBadge";

import type {
    AdminInventoryStatus,
    AdminInventoryStatusHistoryItem,
} from "@/types/adminInventory";

type AdminInventoryHistoryProps = {
    history: AdminInventoryStatusHistoryItem[];

    labels: {
        title: string;
        empty: string;
        system: string;
        noNote: string;

        statuses: Record<
            AdminInventoryStatus,
            string
        >;
    };
};

export function AdminInventoryHistory({
    history,
    labels,
}: AdminInventoryHistoryProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <Heading level={2} size="md">
                {labels.title}
            </Heading>

            {history.length === 0 ? (
                <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    {labels.empty}
                </p>
            ) : (
                <div className="mt-5 space-y-4">
                    {history.map((entry) => (
                        <div
                            key={entry.id}
                            className="rounded-lg border border-slate-200 p-4 dark:border-slate-800"
                        >
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex flex-wrap items-center gap-2">
                                    {entry.from_status && (
                                        <>
                                            <StatusBadge
                                                status={
                                                    entry.from_status
                                                }
                                                label={
                                                    labels.statuses[
                                                        entry.from_status
                                                    ]
                                                }
                                            />

                                            <span className="text-slate-400">
                                                →
                                            </span>
                                        </>
                                    )}

                                    <StatusBadge
                                        status={entry.to_status}
                                        label={
                                            labels.statuses[
                                                entry.to_status
                                            ]
                                        }
                                    />
                                </div>

                                <time className="text-xs text-slate-500 dark:text-slate-400">
                                    {new Intl.DateTimeFormat(
                                        "hu-HU",
                                        {
                                            dateStyle: "medium",
                                            timeStyle: "short",
                                        }
                                    ).format(
                                        new Date(
                                            entry.created_at
                                        )
                                    )}
                                </time>
                            </div>

                            <p className="mt-3 text-sm text-slate-700 dark:text-slate-300">
                                {entry.note ??
                                    labels.noNote}
                            </p>

                            <p className="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {entry.changed_by?.name ??
                                    labels.system}
                            </p>
                        </div>
                    ))}
                </div>
            )}
        </section>
    );
}