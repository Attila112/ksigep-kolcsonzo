import type {
    AdminBatteryStatus,
    AdminBatteryStatusHistory,
} from "@/types/adminBattery";

type AdminBatteryHistoryProps = {
    history: AdminBatteryStatusHistory[];

    labels: {
        title: string;
        empty: string;
        system: string;
        note: string;

        statuses: Record<
            AdminBatteryStatus,
            string
        >;
    };
};

export function AdminBatteryHistory({
    history,
    labels,
}: AdminBatteryHistoryProps) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
                {labels.title}
            </h2>

            {history.length === 0 ? (
                <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    {labels.empty}
                </p>
            ) : (
                <div className="mt-5 space-y-4">
                    {history.map((item) => (
                        <article
                            key={item.id}
                            className="rounded-md border border-slate-200 p-4 dark:border-slate-800"
                        >
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div className="min-w-0">
                                    <p className="font-medium text-slate-950 dark:text-white">
                                        {
                                            labels
                                                .statuses[
                                                item.from_status
                                            ]
                                        }
                                        {" → "}
                                        {
                                            labels
                                                .statuses[
                                                item.to_status
                                            ]
                                        }
                                    </p>

                                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {item.changed_by
                                            ?.name ??
                                            labels.system}
                                    </p>
                                </div>

                                <time className="shrink-0 text-sm text-slate-500 dark:text-slate-400">
                                    {new Intl.DateTimeFormat(
                                        "hu-HU",
                                        {
                                            dateStyle:
                                                "medium",
                                            timeStyle:
                                                "short",
                                        }
                                    ).format(
                                        new Date(
                                            item.created_at
                                        )
                                    )}
                                </time>
                            </div>

                            {item.note && (
                                <div className="mt-3 border-t border-slate-200 pt-3 dark:border-slate-800">
                                    <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        {labels.note}
                                    </p>

                                    <p className="mt-1 whitespace-pre-wrap wrap-break-word text-sm text-slate-700 dark:text-slate-300">
                                        {item.note}
                                    </p>
                                </div>
                            )}
                        </article>
                    ))}
                </div>
            )}
        </section>
    );
}