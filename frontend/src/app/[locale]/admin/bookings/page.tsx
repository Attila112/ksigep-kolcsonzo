import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminBookingStats } from "@/components/admin/bookings/AdminBookingStats";
import { AdminBookingTable } from "@/components/admin/bookings/AdminBookingTable";
import { Heading } from "@/components/ui/Heading";

import {
    getAdminBookings,
} from "@/services/adminBookingService";

type AdminBookingsPageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function AdminBookingsPage({
    params,
}: AdminBookingsPageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    const t =
        await getTranslations("Admin");

    const {
        bookings,
    } = await getAdminBookings();

    const statusLabels = {
        PENDING: t(
            "bookings.status.pending"
        ),
        CONFIRMED: t(
            "bookings.status.confirmed"
        ),
        REJECTED: t(
            "bookings.status.rejected"
        ),
        CANCELLED: t(
            "bookings.status.cancelled"
        ),
        ACTIVE: t(
            "bookings.status.active"
        ),
        COMPLETED: t(
            "bookings.status.completed"
        ),
    };

    const pickupTypeLabels = {
        SELF_PICKUP: t(
            "bookings.pickupType.selfPickup"
        ),
        DELIVERY: t(
            "bookings.pickupType.delivery"
        ),
    };

    return (
        <div className="mx-auto w-full max-w-[1800px] p-4 sm:p-5 lg:p-6">
            <div className="mb-6">
                <Heading
                    level={1}
                    size="lg"
                >
                    {t(
                        "bookings.title"
                    )}
                </Heading>

                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {t(
                        "bookings.description"
                    )}
                </p>
            </div>

            <AdminBookingStats
                bookings={bookings}
                labels={{
                    total: t(
                        "bookings.stats.total"
                    ),
                    pending: t(
                        "bookings.stats.pending"
                    ),
                    active: t(
                        "bookings.stats.active"
                    ),
                    completed: t(
                        "bookings.stats.completed"
                    ),
                }}
            />

            <AdminBookingTable
                bookings={bookings}
                labels={{
                    columns: {
                        id: t(
                            "bookings.columns.id"
                        ),
                        customer: t(
                            "bookings.columns.customer"
                        ),
                        startDate: t(
                            "bookings.columns.startDate"
                        ),
                        endDate: t(
                            "bookings.columns.endDate"
                        ),
                        pickupType: t(
                            "bookings.columns.pickupType"
                        ),
                        status: t(
                            "bookings.columns.status"
                        ),
                        total: t(
                            "bookings.columns.total"
                        ),
                        action: t(
                            "bookings.columns.action"
                        ),
                    },

                    statuses:
                        statusLabels,

                    pickupTypes:
                        pickupTypeLabels,

                    open: t(
                        "bookings.open"
                    ),

                    empty: t(
                        "bookings.empty"
                    ),
                }}
            />
        </div>
    );
}