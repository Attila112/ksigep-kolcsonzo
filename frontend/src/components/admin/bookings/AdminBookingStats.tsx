import Link from "next/link";

import { StatCard } from "@/components/ui/StatCard";

import type {
    AdminBooking,
} from "@/types/adminBooking";

type AdminBookingStatsProps = {
    bookings: AdminBooking[];
    locale: string;
    activeStatus?: string;

    labels: {
        total: string;
        pending: string;
        active: string;
        completed: string;
    };
};

export function AdminBookingStats({
    bookings,
    locale,
    activeStatus,
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
            <Link
                href={`/${locale}/admin/bookings`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.total}
                    value={total}
                    active={!activeStatus}
                />
            </Link>

            <Link
                href={`/${locale}/admin/bookings?status=PENDING`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.pending}
                    value={pending}
                    active={
                        activeStatus ===
                        "PENDING"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/bookings?status=ACTIVE`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.active}
                    value={active}
                    active={
                        activeStatus ===
                        "ACTIVE"
                    }
                />
            </Link>

            <Link
                href={`/${locale}/admin/bookings?status=COMPLETED`}
                className="rounded-lg transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <StatCard
                    title={labels.completed}
                    value={completed}
                    active={
                        activeStatus ===
                        "COMPLETED"
                    }
                />
            </Link>
        </div>
    );
}