"use client";

import {
    useEffect,
    useState,
} from "react";
import { useTranslations } from "next-intl";

import {
    loadBookingSuccess,
} from "@/core/booking/bookingSuccessStorage";
import { Link } from "@/core/i18n/navigation";

import type {
    BookingSuccessState,
} from "@/types/booking";

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

function formatDate(
    value: string
): string {
    const datePart =
        value.slice(0, 10);

    const [
        year,
        month,
        day,
    ] = datePart
        .split("-")
        .map(Number);

    if (
        !year ||
        !month ||
        !day
    ) {
        return value;
    }

    const date = new Date(
        year,
        month - 1,
        day
    );

    if (
        Number.isNaN(
            date.getTime()
        )
    ) {
        return value;
    }

    return new Intl.DateTimeFormat(
        "hu-HU"
    ).format(date);
}

export function BookingSuccess() {
    const t = useTranslations(
        "Booking.success"
    );

    const [
        booking,
        setBooking,
    ] = useState<
        BookingSuccessState | null
    >(null);

    const [
        loaded,
        setLoaded,
    ] = useState(false);

    useEffect(() => {
        queueMicrotask(() => {
            setBooking(
                loadBookingSuccess()
            );

            setLoaded(true);
        });
    }, []);

    if (!loaded) {
        return (
            <div className="py-16 text-center text-sm text-slate-500">
                {t("loading")}
            </div>
        );
    }

    if (!booking) {
        return (
            <section className="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-8">
                <h1 className="text-xl font-semibold text-slate-900">
                    {t(
                        "missing.title"
                    )}
                </h1>

                <p className="mt-2 text-sm text-slate-600">
                    {t(
                        "missing.description"
                    )}
                </p>

                <Link
                    href="/products"
                    className="mt-6 inline-flex rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
                >
                    {t(
                        "backToProducts"
                    )}
                </Link>
            </section>
        );
    }

    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <div className="text-center">
                <div className="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">
                    ✓
                </div>

                <h1 className="mt-5 text-2xl font-bold text-slate-900 sm:text-3xl">
                    {t("title")}
                </h1>

                <p className="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">
                    {t(
                        "description",
                        {
                            email:
                                booking.customerEmail,
                        }
                    )}
                </p>
            </div>

            <div className="mt-8 rounded-xl bg-slate-50 p-4 sm:p-6">
                <div className="grid gap-5 sm:grid-cols-2">
                    <SuccessValue
                        label={t(
                            "bookingId"
                        )}
                        value={`#${booking.bookingId}`}
                    />

                    <SuccessValue
                        label={t(
                            "status"
                        )}
                        value={t(
                            "pending"
                        )}
                    />

                    <SuccessValue
                        label={t(
                            "period"
                        )}
                        value={`${formatDate(
                            booking.startDate
                        )} – ${formatDate(
                            booking.endDate
                        )}`}
                    />

                    <SuccessValue
                        label={t(
                            "pickupType"
                        )}
                        value={
                            booking.pickupType ===
                                "SELF_PICKUP"
                                ? t(
                                    "selfPickup"
                                )
                                : t(
                                    "delivery"
                                )
                        }
                    />
                </div>

                <div className="mt-6 space-y-3 border-t border-slate-200 pt-5">
                    <SummaryRow
                        label={t(
                            "rentalTotal"
                        )}
                        value={formatPrice(
                            booking.rentalTotal
                        )}
                    />

                    <SummaryRow
                        label={t(
                            "depositTotal"
                        )}
                        value={formatPrice(
                            booking.depositTotal
                        )}
                    />

                    <div className="flex items-center justify-between gap-4 border-t border-slate-200 pt-4">
                        <span className="font-semibold text-slate-900">
                            {t(
                                "total"
                            )}
                        </span>

                        <span className="text-lg font-bold text-slate-900">
                            {formatPrice(
                                booking.totalPayable
                            )}
                        </span>
                    </div>
                </div>
            </div>

            <div className="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-900">
                {t(
                    "pendingInfo"
                )}
            </div>

            <div className="mt-8 flex justify-center">
                <Link
                    href="/products"
                    className="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    {t(
                        "backToProducts"
                    )}
                </Link>
            </div>
        </section>
    );
}

type SuccessValueProps = {
    label: string;
    value: string;
};

function SuccessValue({
    label,
    value,
}: SuccessValueProps) {
    return (
        <div>
            <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                {label}
            </p>

            <p className="mt-1 font-medium text-slate-900">
                {value}
            </p>
        </div>
    );
}

type SummaryRowProps = {
    label: string;
    value: string;
};

function SummaryRow({
    label,
    value,
}: SummaryRowProps) {
    return (
        <div className="flex items-center justify-between gap-4 text-sm">
            <span className="text-slate-600">
                {label}
            </span>

            <span className="font-medium text-slate-900">
                {value}
            </span>
        </div>
    );
}