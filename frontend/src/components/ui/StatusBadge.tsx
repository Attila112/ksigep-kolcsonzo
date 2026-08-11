type Status =
    | "AVAILABLE"
    | "RENTED"
    | "INSPECTION"
    | "MAINTENANCE"
    | "DAMAGED"
    | "INACTIVE"
    | "ACTIVE"
    | "PENDING";

type StatusBadgeProps = {
    status: Status;
    label?: string;
};

const styles: Record<Status, string> = {
    AVAILABLE:
        "bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300",

    RENTED:
        "bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300",

    INSPECTION:
        "bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300",

    MAINTENANCE:
        "bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300",

    DAMAGED:
        "bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300",

    INACTIVE:
        "bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300",

    ACTIVE:
        "bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300",

    PENDING:
        "bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300",
};

export function StatusBadge({
    status,
    label,
}: StatusBadgeProps) {
    return (
        <span
            className={[
                "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold",
                styles[status],
            ].join(" ")}
        >
            {label ?? status}
        </span>
    );
}