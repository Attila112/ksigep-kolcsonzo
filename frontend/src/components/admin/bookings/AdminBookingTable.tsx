import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableHead,
    DataTableHeader,
    DataTableRow,
} from "@/components/ui/DataTable";
import { BookingStatusBadge } from "@/components/admin/bookings/BookingStatusBadge";

import { ButtonLink } from "@/components/ui/ButtonLink";

import {
    formatDate,
} from "@/utils/formatDate";

import type {
    AdminBooking,
    AdminBookingPickupType,
    AdminBookingStatus,
} from "@/types/adminBooking";

type AdminBookingTableProps = {
    bookings: AdminBooking[];

    labels: {
        columns: {
            id: string;
            customer: string;
            startDate: string;
            endDate: string;
            pickupType: string;
            status: string;
            total: string;
            action: string;
        };

        statuses: Record<
            AdminBookingStatus,
            string
        >;

        pickupTypes: Record<
            AdminBookingPickupType,
            string
        >;

        open: string;
        empty: string;
    };
};

export function AdminBookingTable({
    bookings,
    labels,
}: AdminBookingTableProps) {
    if (bookings.length === 0) {
        return (
            <div className="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                {labels.empty}
            </div>
        );
    }

    return (
        <DataTable className="min-w-250">
            <DataTableHeader>
                <DataTableRow>
                    <DataTableHead>
                        {labels.columns.id}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.customer}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.startDate}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.endDate}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.pickupType}
                    </DataTableHead>

                    <DataTableHead>
                        {labels.columns.status}
                    </DataTableHead>

                    <DataTableHead className="text-right">
                        {labels.columns.total}
                    </DataTableHead>

                    <DataTableHead className="sticky right-0 bg-slate-50 text-right dark:bg-slate-900">
                        {labels.columns.action}
                    </DataTableHead>
                </DataTableRow>
            </DataTableHeader>

            <DataTableBody>
                {bookings.map(
                    (booking) => (
                        <DataTableRow
                            key={booking.id}
                        >
                            <DataTableCell className="font-medium text-slate-950 dark:text-white">
                                #{booking.id}
                            </DataTableCell>

                            <DataTableCell>
                                <div>
                                    <p className="font-medium text-slate-950 dark:text-white">
                                        {
                                            booking.customer_name
                                        }
                                    </p>

                                    <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {
                                            booking.customer_email
                                        }
                                    </p>
                                </div>
                            </DataTableCell>

                            <DataTableCell>
                                {formatDate(
                                    booking.start_date
                                )}
                            </DataTableCell>

                            <DataTableCell>
                                {formatDate(
                                    booking.end_date
                                )}
                            </DataTableCell>

                            <DataTableCell>
                                {
                                    labels.pickupTypes[
                                    booking.pickup_type
                                    ]
                                }
                            </DataTableCell>

                            <DataTableCell>
                                <BookingStatusBadge
                                    status={
                                        booking.status
                                    }
                                    label={
                                        labels.statuses[
                                        booking.status
                                        ]
                                    }
                                />
                            </DataTableCell>

                            <DataTableCell className="text-right font-medium text-slate-950 dark:text-white">
                                {formatPrice(
                                    booking.total_payable
                                )}
                            </DataTableCell>

                            <DataTableCell className="sticky right-0 bg-white text-right dark:bg-slate-950">
                                <ButtonLink
                                    href={`/admin/bookings/${booking.id}`}
                                    variant="secondary"
                                    size="sm"
                                >
                                    {
                                        labels.open
                                    }
                                </ButtonLink>
                            </DataTableCell>
                        </DataTableRow>
                    )
                )}
            </DataTableBody>
        </DataTable>
    );
}


function formatPrice(
    value: number
): string {
    return new Intl.NumberFormat(
        "hu-HU",
        {
            style: "currency",
            currency: "HUF",
            maximumFractionDigits: 0,
        }
    ).format(value);
}