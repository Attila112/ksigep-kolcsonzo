import type {
    AdminBooking,
} from "@/types/adminBooking";
import {
    formatDate,
    formatDateTime,
} from "@/utils/formatDate";

type AdminBookingGeneralCardProps = {
    booking: AdminBooking;
    labels: {
        title: string;
        customerName: string;
        customerEmail: string;
        customerPhone: string;
        startDate: string;
        endDate: string;
        pickupType: string;
        plannedPickupAt: string;
        selfPickup: string;
        delivery: string;
        noValue: string;
    };
};

export function AdminBookingGeneralCard({
    booking,
    labels,
}: AdminBookingGeneralCardProps) {
    const pickupTypeLabel =
        booking.pickup_type === "SELF_PICKUP"
            ? labels.selfPickup
            : labels.delivery;

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
                {labels.title}
            </h2>

            <dl className="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <DetailItem
                    label={labels.customerName}
                    value={booking.customer_name}
                />

                <DetailItem
                    label={labels.customerEmail}
                    value={booking.customer_email}
                />

                <DetailItem
                    label={labels.customerPhone}
                    value={booking.customer_phone}
                />

                <DetailItem
                    label={labels.pickupType}
                    value={pickupTypeLabel}
                />

                <DetailItem
                    label={labels.startDate}
                    value={formatDate(booking.start_date)}
                />

                <DetailItem
                    label={labels.endDate}
                    value={formatDate(booking.end_date)}
                />

                <DetailItem
                    label={labels.plannedPickupAt}
                    value={
                        booking.planned_pickup_at
                            ? formatDateTime(
                                booking.planned_pickup_at
                            )
                            : labels.noValue
                    }
                />
            </dl>
        </section>
    );
}

type DetailItemProps = {
    label: string;
    value: string;
};

function DetailItem({
    label,
    value,
}: DetailItemProps) {
    return (
        <div>
            <dt className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                {label}
            </dt>

            <dd className="mt-1 wrap-break-word text-sm font-medium text-slate-900 dark:text-slate-100">
                {value}
            </dd>
        </div>
    );
}