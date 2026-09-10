import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminBookingGeneralCard } from "@/components/admin/bookings/detail/AdminBookingGeneralCard";
import { AdminBookingHeader } from "@/components/admin/bookings/detail/AdminBookingHeader";
import { AdminBookingItems } from "@/components/admin/bookings/detail/AdminBookingItems";

import { getAdminBooking } from "@/services/adminBookingService";

type AdminBookingDetailPageProps = {
    params: Promise<{
        locale: string;
        bookingId: string;
    }>;
};

export default async function AdminBookingDetailPage({
    params,
}: AdminBookingDetailPageProps) {
    const {
        locale,
        bookingId,
    } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    const bookingIdNumber =
        Number(bookingId);

    const {
        booking,
    } = await getAdminBooking(
        bookingIdNumber
    );

    const bookingStatusLabels = {
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
    const inventoryStatusLabels = {
        AVAILABLE: t(
            "inventoryStatus.available"
        ),
        RENTED: t(
            "inventoryStatus.rented"
        ),
        INSPECTION: t(
            "inventoryStatus.inspection"
        ),
        MAINTENANCE: t(
            "inventoryStatus.maintenance"
        ),
        DAMAGED: t(
            "inventoryStatus.damaged"
        ),
        INACTIVE: t(
            "inventoryStatus.inactive"
        ),
    };

    return (
        <div className="mx-auto w-full max-w-[1800px] p-4 sm:p-5 lg:p-6">
            <AdminBookingHeader
                bookingId={booking.id}
                status={booking.status}
                labels={{
                    back: t(
                        "bookings.detail.back"
                    ),
                    booking: t(
                        "bookings.detail.booking"
                    ),
                    statuses:
                        bookingStatusLabels,
                }}
            />

            <div className="mt-6 space-y-6">
                <AdminBookingGeneralCard
                    booking={booking}
                    labels={{
                        title: t(
                            "bookings.detail.generalInformation"
                        ),
                        customerName: t(
                            "bookings.detail.customerName"
                        ),
                        customerEmail: t(
                            "bookings.detail.customerEmail"
                        ),
                        customerPhone: t(
                            "bookings.detail.customerPhone"
                        ),
                        startDate: t(
                            "bookings.detail.startDate"
                        ),
                        endDate: t(
                            "bookings.detail.endDate"
                        ),
                        pickupType: t(
                            "bookings.detail.pickupType"
                        ),
                        plannedPickupAt: t(
                            "bookings.detail.plannedPickupAt"
                        ),
                        selfPickup: t(
                            "bookings.pickupType.selfPickup"
                        ),
                        delivery: t(
                            "bookings.pickupType.delivery"
                        ),
                        noValue: t(
                            "bookings.detail.noValue"
                        ),
                    }}
                />

                <AdminBookingItems
                    items={booking.items}
                    labels={{
                        title: t(
                            "bookings.detail.items"
                        ),
                        quantity: t(
                            "bookings.detail.quantity"
                        ),
                        rentalDays: t(
                            "bookings.detail.rentalDays"
                        ),
                        inventoryItem: t(
                            "bookings.detail.inventoryItem"
                        ),
                        serialNumber: t(
                            "bookings.detail.serialNumber"
                        ),
                        assignedAt: t(
                            "bookings.detail.assignedAt"
                        ),
                        returnedAt: t(
                            "bookings.detail.returnedAt"
                        ),
                        accessories: t(
                            "bookings.detail.accessories"
                        ),
                        battery: t(
                            "batteries.types.battery"
                        ),
                        charger: t(
                            "batteries.types.charger"
                        ),
                        batterySystem: t(
                            "bookings.detail.batterySystem"
                        ),
                        noSerialNumber: t(
                            "bookings.detail.noSerialNumber"
                        ),
                        notReturned: t(
                            "bookings.detail.notReturned"
                        ),
                        noAllocation: t(
                            "bookings.detail.noAllocation"
                        ),
                        statuses:   inventoryStatusLabels,
                    }}
                />
            </div>
        </div>
    );
}