"use client";

import {
    useState,
    useTransition,
} from "react";

import { useRouter } from "next/navigation";

import {
    approveBookingAction,
    rejectBookingAction,
} from "@/core/admin/bookingActions";

import type {
    AdminBookingStatus,
} from "@/types/adminBooking";

type AdminBookingApprovalActionsProps = {
    bookingId: number;
    status: AdminBookingStatus;

    labels: {
        title: string;
        description: string;

        approve: string;
        approving: string;

        rejectReason: string;
        rejectReasonPlaceholder: string;

        reject: string;
        rejecting: string;

        reasonRequired: string;

        approveSuccess: string;
        rejectSuccess: string;

        unknownError: string;
    };
};

export function AdminBookingApprovalActions({
    bookingId,
    status,
    labels,
}: AdminBookingApprovalActionsProps) {
    const router = useRouter();

    const [reason, setReason] =
        useState("");

    const [error, setError] =
        useState<string | null>(null);

    const [success, setSuccess] =
        useState<string | null>(null);

    const [
        isApproving,
        startApproveTransition,
    ] = useTransition();

    const [
        isRejecting,
        startRejectTransition,
    ] = useTransition();

    if (status !== "PENDING") {
        return null;
    }

    function handleApprove() {
        setError(null);
        setSuccess(null);

        startApproveTransition(
            async () => {
                const result =
                    await approveBookingAction(
                        bookingId
                    );

                if (!result.success) {
                    setError(
                        result.message ===
                            "UNKNOWN_ERROR"
                            ? labels.unknownError
                            : result.message
                    );

                    return;
                }

                setSuccess(
                    labels.approveSuccess
                );

                router.refresh();
            }
        );
    }

    function handleReject() {
        setError(null);
        setSuccess(null);

        const trimmedReason =
            reason.trim();

        if (!trimmedReason) {
            setError(
                labels.reasonRequired
            );

            return;
        }

        startRejectTransition(
            async () => {
                const result =
                    await rejectBookingAction(
                        bookingId,
                        trimmedReason
                    );

                if (!result.success) {
                    setError(
                        result.message ===
                            "UNKNOWN_ERROR"
                            ? labels.unknownError
                            : result.message
                    );

                    return;
                }

                setReason("");

                setSuccess(
                    labels.rejectSuccess
                );

                router.refresh();
            }
        );
    }

    const isBusy =
        isApproving ||
        isRejecting;

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
            <div className="mb-5">
                <h2 className="text-base font-semibold text-slate-950 dark:text-white">
                    {labels.title}
                </h2>

                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {labels.description}
                </p>
            </div>

            {error && (
                <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                    {error}
                </div>
            )}

            {success && (
                <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {success}
                </div>
            )}

            <div className="grid gap-5 lg:grid-cols-2">
                <div>
                    <button
                        type="button"
                        onClick={
                            handleApprove
                        }
                        disabled={
                            isBusy
                        }
                        className="inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {isApproving
                            ? labels.approving
                            : labels.approve}
                    </button>
                </div>

                <div>
                    <label
                        htmlFor="booking-reject-reason"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {
                            labels.rejectReason
                        }
                    </label>

                    <textarea
                        id="booking-reject-reason"
                        value={reason}
                        onChange={(event) =>
                            setReason(
                                event.target
                                    .value
                            )
                        }
                        disabled={isBusy}
                        rows={4}
                        placeholder={
                            labels.rejectReasonPlaceholder
                        }
                        className="w-full resize-y rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-slate-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                    />

                    <button
                        type="button"
                        onClick={
                            handleReject
                        }
                        disabled={
                            isBusy
                        }
                        className="mt-3 inline-flex min-h-10 items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900 dark:bg-slate-950 dark:text-red-300 dark:hover:bg-red-950/40"
                    >
                        {isRejecting
                            ? labels.rejecting
                            : labels.reject}
                    </button>
                </div>
            </div>
        </section>
    );
}