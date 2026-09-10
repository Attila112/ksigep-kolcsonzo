import type {
    AdminBookingStatus,
} from "@/types/adminBooking";

type BookingStatusBadgeProps = {
    status: AdminBookingStatus;
    label: string;
};

export function BookingStatusBadge({
    status,
    label,
}: BookingStatusBadgeProps) {
    const statusClassNames: Record<
        AdminBookingStatus,
        string
    > = {
        PENDING:
            "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300",

        CONFIRMED:
            "border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300",

        REJECTED:
            "border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300",

        CANCELLED:
            "border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300",

        ACTIVE:
            "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300",

        COMPLETED:
            "border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900/60 dark:bg-violet-950/40 dark:text-violet-300",
    };

    return (
        <span
            className={`inline-flex whitespace-nowrap rounded-full border px-3 py-1.5 text-sm font-medium ${statusClassNames[status]}`}
        >
            {label}
        </span>
    );
}