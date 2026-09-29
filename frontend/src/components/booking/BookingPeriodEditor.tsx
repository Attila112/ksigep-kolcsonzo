"use client";

import {
    useEffect,
    useState,
} from "react";
import { useTranslations } from "next-intl";
import { hu } from "react-day-picker/locale";
import type { DateRange } from "react-day-picker";

import { Calendar } from "@/components/ui/calendar";
import { checkBookingAvailability } from "@/services/bookingAvailabilityService";

import type {
    BookingCartItem,
    BookingCartPeriod,
} from "@/types/bookingCart";

type BookingPeriodEditorProps = {
    period: BookingCartPeriod;
    items: BookingCartItem[];
    disabled?: boolean;
    onPeriodChange: (
        period: BookingCartPeriod
    ) => void;
};

function formatDateKey(
    date: Date
): string {
    const year = date.getFullYear();

    const month = String(
        date.getMonth() + 1
    ).padStart(2, "0");

    const day = String(
        date.getDate()
    ).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function parseDateKey(
    value: string
): Date {
    const [year, month, day] =
        value.split("-").map(Number);

    return new Date(
        year,
        month - 1,
        day
    );
}

function formatDate(
    date: Date
): string {
    return new Intl.DateTimeFormat(
        "hu-HU"
    ).format(date);
}

function useTwoMonthCalendar(): boolean {
    const [
        showTwoMonths,
        setShowTwoMonths,
    ] = useState(false);

    useEffect(() => {
        const mediaQuery =
            window.matchMedia(
                "(min-width: 1280px)"
            );

        const update = () => {
            setShowTwoMonths(
                mediaQuery.matches
            );
        };

        update();

        mediaQuery.addEventListener(
            "change",
            update
        );

        return () => {
            mediaQuery.removeEventListener(
                "change",
                update
            );
        };
    }, []);

    return showTwoMonths;
}

export function BookingPeriodEditor({
    period,
    items,
    disabled = false,
    onPeriodChange,
}: BookingPeriodEditorProps) {
    const t = useTranslations(
        "Booking.cart"
    );

    const showTwoMonths =
        useTwoMonthCalendar();

    const [editing, setEditing] =
        useState(false);

    const [saving, setSaving] =
        useState(false);

    const [error, setError] =
        useState<
            "unavailable" |
            "check" |
            null
        >(null);

    const [
        selectedRange,
        setSelectedRange,
    ] = useState<
        DateRange | undefined
    >();

    const [month, setMonth] =
        useState(() => {
            const startDate =
                parseDateKey(
                    period.startDate
                );

            return new Date(
                startDate.getFullYear(),
                startDate.getMonth(),
                1
            );
        });

    function openEditor(): void {
        const startDate =
            parseDateKey(
                period.startDate
            );

        const endDate =
            parseDateKey(
                period.endDate
            );

        setSelectedRange({
            from: startDate,
            to: endDate,
        });

        setMonth(
            new Date(
                startDate.getFullYear(),
                startDate.getMonth(),
                1
            )
        );

        setError(null);
        setEditing(true);
    }

    function closeEditor(): void {
        if (saving) {
            return;
        }

        setEditing(false);
        setError(null);
    }

    async function handleSave(): Promise<void> {
        if (
            !selectedRange?.from ||
            !selectedRange.to ||
            saving
        ) {
            return;
        }

        const nextPeriod: BookingCartPeriod =
        {
            startDate:
                formatDateKey(
                    selectedRange.from
                ),
            endDate:
                formatDateKey(
                    selectedRange.to
                ),
        };

        /*
         * Ha ugyanazt az időszakot választotta,
         * nincs szükség új availability kérésre.
         */
        if (
            nextPeriod.startDate ===
                period.startDate &&
            nextPeriod.endDate ===
                period.endDate
        ) {
            setEditing(false);
            setError(null);

            return;
        }

        setSaving(true);
        setError(null);

        try {
            const response =
                await checkBookingAvailability({
                    start_date:
                        nextPeriod.startDate,
                    end_date:
                        nextPeriod.endDate,
                    items: items.map(
                        (item) => ({
                            product_id:
                                item.productId,
                        })
                    ),
                });

            const availabilityByProduct =
                new Map(
                    response.items.map(
                        (availability) => [
                            availability.product_id,
                            availability,
                        ]
                    )
                );

            const unavailable =
                items.some((item) => {
                    const availability =
                        availabilityByProduct.get(
                            item.productId
                        );

                    if (!availability) {
                        return true;
                    }

                    return (
                        !availability.available ||
                        item.quantity >
                            availability.available_quantity
                    );
                });

            if (unavailable) {
                setError("unavailable");

                return;
            }

            onPeriodChange(
                nextPeriod
            );

            setEditing(false);
            setError(null);
        } catch {
            setError("check");
        } finally {
            setSaving(false);
        }
    }

    return (
        <div className="mt-4">
            <div className="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                <p className="text-sm font-medium text-slate-900">
                    {t(
                        "commonPeriodTitle"
                    )}
                </p>

                <p className="mt-1 text-sm leading-6 text-slate-600">
                    {t(
                        "commonPeriodDescription"
                    )}
                </p>

                {!editing && (
                    <button
                        type="button"
                        onClick={openEditor}
                        disabled={disabled}
                        className="mt-4 rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {t(
                            "changePeriod"
                        )}
                    </button>
                )}
            </div>

            {editing && (
                <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div>
                        <h2 className="text-lg font-semibold text-slate-900">
                            {t(
                                "changePeriodTitle"
                            )}
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            {t(
                                "changePeriodDescription"
                            )}
                        </p>
                    </div>

                    <div className="mt-6">
                        <Calendar
                            mode="range"
                            locale={hu}
                            month={month}
                            onMonthChange={
                                setMonth
                            }
                            selected={
                                selectedRange
                            }
                            onSelect={(
                                range
                            ) => {
                                setSelectedRange(
                                    range
                                );

                                setError(
                                    null
                                );
                            }}
                            numberOfMonths={
                                showTwoMonths
                                    ? 2
                                    : 1
                            }
                            className="mx-auto"
                        />
                    </div>

                    {selectedRange?.from &&
                        selectedRange.to && (
                            <div className="mt-6 rounded-xl bg-slate-50 p-4">
                                <p className="text-sm font-medium text-slate-900">
                                    {t(
                                        "newPeriod"
                                    )}
                                </p>

                                <p className="mt-1 text-sm text-slate-600">
                                    {formatDate(
                                        selectedRange.from
                                    )}
                                    {" – "}
                                    {formatDate(
                                        selectedRange.to
                                    )}
                                </p>
                            </div>
                        )}

                    {error ===
                        "unavailable" && (
                        <p className="mt-4 text-sm font-medium text-red-600">
                            {t(
                                "periodUnavailable"
                            )}
                        </p>
                    )}

                    {error === "check" && (
                        <p className="mt-4 text-sm font-medium text-red-600">
                            {t(
                                "periodCheckError"
                            )}
                        </p>
                    )}

                    <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            onClick={
                                closeEditor
                            }
                            disabled={
                                saving
                            }
                            className="rounded-lg border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {t(
                                "cancelPeriodChange"
                            )}
                        </button>

                        <button
                            type="button"
                            onClick={() =>
                                void handleSave()
                            }
                            disabled={
                                saving ||
                                !selectedRange
                                    ?.from ||
                                !selectedRange
                                    .to
                            }
                            className="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {saving
                                ? t(
                                      "savingPeriod"
                                  )
                                : t(
                                      "savePeriod"
                                  )}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}