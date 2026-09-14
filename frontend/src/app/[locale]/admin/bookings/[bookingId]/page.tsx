import {
    getTranslations,
    setRequestLocale,
} from "next-intl/server";

import { AdminBookingGeneralCard } from "@/components/admin/bookings/detail/AdminBookingGeneralCard";
import { AdminBookingHeader } from "@/components/admin/bookings/detail/AdminBookingHeader";
import { AdminBookingItems } from "@/components/admin/bookings/detail/AdminBookingItems";
import { AdminBookingApprovalActions } from "@/components/admin/bookings/detail/AdminBookingApprovalActions";
import { AdminBookingIssueActions } from "@/components/admin/bookings/detail/AdminBookingIssueActions";
import { AdminBookingReturnActions } from "@/components/admin/bookings/detail/AdminBookingReturnActions";

import { getAdminBatteryItems } from "@/services/adminBatteryService";
import { getAdminInventoryItems } from "@/services/adminInventoryService";

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
    const issueResources =
        booking.status === "CONFIRMED"
            ? await Promise.all([
                getAdminInventoryItems(),
                getAdminBatteryItems(),
            ])
            : null;

    const inventoryItems =
        issueResources?.[0]
            .inventory_items ?? [];

    const batteryItems =
        issueResources?.[1]
            .battery_items ?? [];

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
                <AdminBookingApprovalActions
                    bookingId={booking.id}
                    status={booking.status}
                    labels={{
                        title: t(
                            "bookings.detail.approval.title"
                        ),
                        description: t(
                            "bookings.detail.approval.description"
                        ),

                        approve: t(
                            "bookings.detail.approval.approve"
                        ),
                        approving: t(
                            "bookings.detail.approval.approving"
                        ),

                        rejectReason: t(
                            "bookings.detail.approval.rejectReason"
                        ),
                        rejectReasonPlaceholder: t(
                            "bookings.detail.approval.rejectReasonPlaceholder"
                        ),

                        reject: t(
                            "bookings.detail.approval.reject"
                        ),
                        rejecting: t(
                            "bookings.detail.approval.rejecting"
                        ),

                        reasonRequired: t(
                            "bookings.detail.approval.reasonRequired"
                        ),

                        approveSuccess: t(
                            "bookings.detail.approval.approveSuccess"
                        ),
                        rejectSuccess: t(
                            "bookings.detail.approval.rejectSuccess"
                        ),

                        unknownError: t(
                            "bookings.detail.approval.unknownError"
                        ),
                    }}
                />
                <AdminBookingIssueActions
                    bookingId={booking.id}
                    status={booking.status}
                    items={booking.items}
                    inventoryItems={
                        inventoryItems
                    }
                    batteryItems={
                        batteryItems
                    }
                    labels={{
                        title: t(
                            "bookings.detail.issue.title"
                        ),
                        description: t(
                            "bookings.detail.issue.description"
                        ),
                        product: t(
                            "bookings.detail.issue.product"
                        ),
                        machine: t(
                            "bookings.detail.issue.machine"
                        ),
                        machineNumber: t(
                            "bookings.detail.issue.machineNumber"
                        ),
                        selectMachine: t(
                            "bookings.detail.issue.selectMachine"
                        ),
                        noMachineAvailable: t(
                            "bookings.detail.issue.noMachineAvailable"
                        ),
                        accessories: t(
                            "bookings.detail.issue.accessories"
                        ),
                        batteries: t(
                            "bookings.detail.issue.batteries"
                        ),
                        chargers: t(
                            "bookings.detail.issue.chargers"
                        ),
                        required: t(
                            "bookings.detail.issue.required"
                        ),
                        selected: t(
                            "bookings.detail.issue.selected"
                        ),
                        noBatteryRequired: t(
                            "bookings.detail.issue.noBatteryRequired"
                        ),
                        noBatteryAvailable: t(
                            "bookings.detail.issue.noBatteryAvailable"
                        ),
                        noChargerAvailable: t(
                            "bookings.detail.issue.noChargerAvailable"
                        ),
                        issue: t(
                            "bookings.detail.issue.issue"
                        ),
                        issuing: t(
                            "bookings.detail.issue.issuing"
                        ),
                        machineRequired: t(
                            "bookings.detail.issue.machineRequired"
                        ),
                        duplicateMachine: t(
                            "bookings.detail.issue.duplicateMachine"
                        ),
                        accessoryRequirementInvalid: t(
                            "bookings.detail.issue.accessoryRequirementInvalid"
                        ),
                        success: t(
                            "bookings.detail.issue.success"
                        ),
                        unknownError: t(
                            "bookings.detail.issue.unknownError"
                        ),
                    }}
                />
                <AdminBookingReturnActions
                    bookingId={booking.id}
                    status={booking.status}
                    items={booking.items}
                    labels={{
                        title: t(
                            "bookings.detail.return.title"
                        ),
                        description: t(
                            "bookings.detail.return.description"
                        ),
                        product: t(
                            "bookings.detail.return.product"
                        ),
                        machine: t(
                            "bookings.detail.return.machine"
                        ),
                        serialNumber: t(
                            "bookings.detail.return.serialNumber"
                        ),
                        accessories: t(
                            "bookings.detail.return.accessories"
                        ),
                        battery: t(
                            "bookings.detail.return.battery"
                        ),
                        charger: t(
                            "bookings.detail.return.charger"
                        ),
                        noSerialNumber: t(
                            "bookings.detail.return.noSerialNumber"
                        ),
                        selectAtLeastOne: t(
                            "bookings.detail.return.selectAtLeastOne"
                        ),
                        returnSelected: t(
                            "bookings.detail.return.returnSelected"
                        ),
                        returning: t(
                            "bookings.detail.return.returning"
                        ),
                        success: t(
                            "bookings.detail.return.success"
                        ),
                        unknownError: t(
                            "bookings.detail.return.unknownError"
                        ),
                    }}
                />
                <AdminBookingItems
                    items={booking.items}
                    locale={locale}
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
                        statuses: inventoryStatusLabels,
                    }}
                />
            </div>
        </div>
    );
}