import { StatCard } from "@/components/ui/StatCard";

import type {
    AdminBooking,
} from "@/types/adminBooking";

type AdminBookingStatsProps = {
    bookings: AdminBooking[];

    labels: {
        total: string;
        pending: string;
        active: string;
        completed: string;
    };
};

export function AdminBookingStats({
    bookings,
    labels,
}: AdminBookingStatsProps) {
    const total = bookings.length;

    const pending = bookings.filter(
        (booking) =>
            booking.status === "PENDING"
    ).length;

    const active = bookings.filter(
        (booking) =>
            booking.status === "ACTIVE"
    ).length;

    const completed = bookings.filter(
        (booking) =>
            booking.status === "COMPLETED"
    ).length;

    return (
        <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            <StatCard
                title={labels.total}
                value={total}
            />

            <StatCard
                title={labels.pending}
                value={pending}
            />

            <StatCard
                title={labels.active}
                value={active}
            />

            <StatCard
                title={labels.completed}
                value={completed}
            />
        </div>
    );
}