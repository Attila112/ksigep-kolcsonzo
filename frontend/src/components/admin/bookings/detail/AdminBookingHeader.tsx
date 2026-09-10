import { BookingStatusBadge } from "@/components/admin/bookings/BookingStatusBadge";
import { Link } from "@/core/i18n/navigation";

import type {
    AdminBookingStatus,
} from "@/types/adminBooking";

type AdminBookingHeaderProps = {
    bookingId: number;
    status: AdminBookingStatus;
    labels: {
        back: string;
        booking: string;
        statuses: Record<
            AdminBookingStatus,
            string
        >;
    };
};

export function AdminBookingHeader({
    bookingId,
    status,
    labels,
}: AdminBookingHeaderProps) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <Link
                    href="/admin/bookings"
                    className="text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                >
                    ← {labels.back}
                </Link>

                <h1 className="mt-3 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                    {labels.booking} #{bookingId}
                </h1>
            </div>

            <div className="self-start">
                <BookingStatusBadge
                    status={status}
                    label={
                        labels.statuses[
                            status
                        ]
                    }
                />
            </div>
        </div>
    );
}