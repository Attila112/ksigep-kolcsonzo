"use client";

import { useEffect, useMemo, useState } from "react";
import { useTranslations } from "next-intl";
import { hu } from "react-day-picker/locale";
import type { DateRange } from "react-day-picker";

import { Calendar } from "@/components/ui/calendar";
import {
    getProductAvailability,
    getProductAvailabilityCalendar,
    type ProductAvailabilityResponse,
} from "@/services/productAvailabilityService";
import type { BookingCartPeriod } from "@/types/bookingCart";
import type { ProductAvailabilityDay } from "@/types/productAvailability";
import type { ProductBookingSelection } from "@/types/productBooking";

type ProductAvailabilityCalendarProps = {
    productId: number;
    onSelectionChange?: (
        selection: ProductBookingSelection | null
    ) => void;
    fixedPeriod?: BookingCartPeriod | null;
    periodLocked?: boolean;
};

type SelectedPeriodAvailability = {
    available: boolean;
    quantity: number;
};

function formatDateKey(date: Date): string {
    const year = date.getFullYear();
    const month = String(
        date.getMonth() + 1
    ).padStart(2, "0");
    const day = String(
        date.getDate()
    ).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function parseDateKey(value: string): Date {
    const [year, month, day] =
        value.split("-").map(Number);

    return new Date(
        year,
        month - 1,
        day
    );
}

function createDateRange(
    period: BookingCartPeriod
): DateRange {
    return {
        from: parseDateKey(
            period.startDate
        ),
        to: parseDateKey(
            period.endDate
        ),
    };
}

function getCalendarRange(month: Date) {
    const startDate = new Date(
        month.getFullYear(),
        month.getMonth(),
        1
    );

    const endDate = new Date(
        month.getFullYear(),
        month.getMonth() + 2,
        0
    );

    return {
        startDate:
            formatDateKey(startDate),
        endDate:
            formatDateKey(endDate),
    };
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

export function ProductAvailabilityCalendar({
    productId,
    onSelectionChange,
    fixedPeriod = null,
    periodLocked = false,
}: ProductAvailabilityCalendarProps) {
    const t = useTranslations(
        "Product.details.availability"
    );

    const showTwoMonths =
        useTwoMonthCalendar();

    const [month, setMonth] =
        useState(() => {
            const initialDate =
                fixedPeriod
                    ? parseDateKey(
                        fixedPeriod.startDate
                    )
                    : new Date();

            return new Date(
                initialDate.getFullYear(),
                initialDate.getMonth(),
                1
            );
        });

    const [
        selectedRange,
        setSelectedRange,
    ] = useState<
        DateRange | undefined
    >(() =>
        fixedPeriod
            ? createDateRange(
                fixedPeriod
            )
            : undefined
    );

    /*
     * Ha van rögzített kosáridőszak, mindig azt
     * használjuk kijelölésként. Így akkor is
     * helyesen működik, ha a sessionStorage-ból
     * csak hydration után érkezik meg a period.
     */
    const fixedStartDate =
        fixedPeriod?.startDate;

    const fixedEndDate =
        fixedPeriod?.endDate;

    const effectiveSelectedRange =
        useMemo<DateRange | undefined>(
            () => {
                if (
                    fixedStartDate &&
                    fixedEndDate
                ) {
                    return {
                        from: parseDateKey(
                            fixedStartDate
                        ),
                        to: parseDateKey(
                            fixedEndDate
                        ),
                    };
                }

                return selectedRange;
            },
            [
                fixedStartDate,
                fixedEndDate,
                selectedRange,
            ]
        );
    const [
        availabilityDays,
        setAvailabilityDays,
    ] = useState<
        ProductAvailabilityDay[]
    >([]);

    const [
        fixedPeriodAvailability,
        setFixedPeriodAvailability,
    ] = useState<
        ProductAvailabilityResponse | null
    >(null);

    const [
        fixedPeriodLoading,
        setFixedPeriodLoading,
    ] = useState(false);

    const [
        fixedPeriodError,
        setFixedPeriodError,
    ] = useState(false);

    const [loading, setLoading] =
        useState(true);
    const [error, setError] =
        useState(false);

    /*
     * A megnyitott naptári hónapok napi
     * elérhetőségének betöltése.
     *
     * Ez kizárólag a naptár vizuális
     * állapotához kell.
     */
    useEffect(() => {
        let cancelled = false;

        async function loadAvailability() {
            setLoading(true);
            setError(false);

            const range =
                getCalendarRange(month);

            try {
                const response =
                    await getProductAvailabilityCalendar(
                        {
                            productId,
                            startDate:
                                range.startDate,
                            endDate:
                                range.endDate,
                        }
                    );

                if (!cancelled) {
                    setAvailabilityDays(
                        response.days
                    );
                }
            } catch {
                if (!cancelled) {
                    setError(true);
                    setAvailabilityDays(
                        []
                    );
                }
            } finally {
                if (!cancelled) {
                    setLoading(false);
                }
            }
        }

        void loadAvailability();

        return () => {
            cancelled = true;
        };
    }, [
        month,
        productId,
    ]);

    /*
     * A kosárban rögzített időszak
     * elérhetőségét külön ellenőrizzük.
     *
     * Fontos: ez nem függ attól, hogy a
     * felhasználó éppen melyik hónapot
     * nézi a naptárban.
     */
    useEffect(() => {
        if (
            !fixedStartDate ||
            !fixedEndDate
        ) {
            return;
        }

        const startDate =
            fixedStartDate;
        const endDate =
            fixedEndDate;

        let cancelled = false;

        async function loadFixedPeriodAvailability() {
            setFixedPeriodLoading(
                true
            );
            setFixedPeriodError(
                false
            );
            setFixedPeriodAvailability(
                null
            );

            try {
                const response =
                    await getProductAvailability(
                        {
                            productId,
                            startDate,
                            endDate,
                        }
                    );

                if (!cancelled) {
                    setFixedPeriodAvailability(
                        response
                    );
                }
            } catch {
                if (!cancelled) {
                    setFixedPeriodError(
                        true
                    );
                    setFixedPeriodAvailability(
                        null
                    );
                }
            } finally {
                if (!cancelled) {
                    setFixedPeriodLoading(
                        false
                    );
                }
            }
        }

        void loadFixedPeriodAvailability();

        return () => {
            cancelled = true;
        };
    }, [
        fixedStartDate,
        fixedEndDate,
        productId,
    ]);

    const availabilityByDate =
        useMemo(
            () =>
                new Map(
                    availabilityDays.map(
                        (day) => [
                            day.date,
                            day,
                        ]
                    )
                ),
            [availabilityDays]
        );

    const unavailableDates = (
        date: Date
    ) => {
        const availability =
            availabilityByDate.get(
                formatDateKey(date)
            );

        return (
            availability ===
            undefined ||
            !availability.available
        );
    };

    const availableDates = (
        date: Date
    ) => {
        const availability =
            availabilityByDate.get(
                formatDateKey(date)
            );

        return (
            availability !==
            undefined &&
            availability.available_quantity >=
            2
        );
    };

    const lowAvailabilityDates = (
        date: Date
    ) => {
        const availability =
            availabilityByDate.get(
                formatDateKey(date)
            );

        return (
            availability !==
            undefined &&
            availability.available_quantity ===
            1
        );
    };

    /*
     * Szabad dátumválasztásnál a naptár
     * napi adataiból számoljuk ki a teljes
     * kijelölt időszak minimum kapacitását.
     */
    const calendarSelectedPeriodAvailability:
        SelectedPeriodAvailability | null =
        (() => {
            if (
                !selectedRange?.from ||
                !selectedRange.to
            ) {
                return null;
            }

            const current =
                new Date(
                    selectedRange.from.getFullYear(),
                    selectedRange.from.getMonth(),
                    selectedRange.from.getDate()
                );

            const end =
                new Date(
                    selectedRange.to.getFullYear(),
                    selectedRange.to.getMonth(),
                    selectedRange.to.getDate()
                );

            let minimumAvailableQuantity =
                Number.POSITIVE_INFINITY;

            while (
                current <= end
            ) {
                const availability =
                    availabilityByDate.get(
                        formatDateKey(
                            current
                        )
                    );

                if (
                    !availability ||
                    !availability.available
                ) {
                    return {
                        available:
                            false,
                        quantity: 0,
                    };
                }

                minimumAvailableQuantity =
                    Math.min(
                        minimumAvailableQuantity,
                        availability.available_quantity
                    );

                current.setDate(
                    current.getDate() +
                    1
                );
            }

            return {
                available: true,
                quantity:
                    minimumAvailableQuantity ===
                        Number.POSITIVE_INFINITY
                        ? 0
                        : minimumAvailableQuantity,
            };
        })();

    /*
     * Rögzített kosáridőszaknál a külön
     * interval endpoint eredménye az
     * irányadó. Emiatt a foglalhatóság
     * nem változik naptárlapozáskor.
     */
    const selectedPeriodAvailability:
        SelectedPeriodAvailability | null =
        fixedPeriod
            ? fixedPeriodAvailability
                ? {
                    available:
                        fixedPeriodAvailability.available,
                    quantity:
                        fixedPeriodAvailability.available_quantity,
                }
                : null
            : calendarSelectedPeriodAvailability;

    useEffect(() => {
        const range =
            effectiveSelectedRange;

        if (
            !range?.from ||
            !range.to ||
            fixedPeriodLoading ||
            fixedPeriodError ||
            !selectedPeriodAvailability?.available ||
            selectedPeriodAvailability.quantity <
            1
        ) {
            onSelectionChange?.(
                null
            );
            return;
        }

        onSelectionChange?.({
            startDate:
                formatDateKey(
                    range.from
                ),
            endDate:
                formatDateKey(
                    range.to
                ),
            availableQuantity:
                selectedPeriodAvailability.quantity,
        });
    }, [
        effectiveSelectedRange,
        fixedPeriodLoading,
        fixedPeriodError,
        selectedPeriodAvailability?.available,
        selectedPeriodAvailability?.quantity,
        onSelectionChange,
    ]);

    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6 lg:p-8">
            <div className="border-b border-slate-100 pb-5">
                <h2 className="text-xl font-semibold text-slate-900">
                    {t("title")}
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    {t(
                        "description"
                    )}
                </p>
            </div>

            {error ? (
                <div className="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {t("error")}
                </div>
            ) : (
                <>
                    <div className="relative mt-6">
                        {loading && (
                            <div className="mb-4 text-center text-sm text-slate-500">
                                {t(
                                    "loading"
                                )}
                            </div>
                        )}

                        <Calendar
                            mode="range"
                            locale={hu}
                            month={month}
                            onMonthChange={
                                setMonth
                            }
                            selected={
                                effectiveSelectedRange
                            }
                            onSelect={(
                                range
                            ) => {
                                if (
                                    periodLocked
                                ) {
                                    return;
                                }

                                setSelectedRange(
                                    range
                                );
                            }}
                            numberOfMonths={
                                showTwoMonths
                                    ? 2
                                    : 1
                            }
                            disabled={
                                unavailableDates
                            }
                            modifiers={{
                                available:
                                    availableDates,
                                lowAvailability:
                                    lowAvailabilityDates,
                                locked:
                                    periodLocked
                                        ? () =>
                                            true
                                        : () =>
                                            false,
                            }}
                            modifiersClassNames={{
                                available:
                                    "availability-available",
                                lowAvailability:
                                    "availability-low",
                                locked:
                                    "pointer-events-none",
                            }}
                            className="mx-auto"
                        />
                    </div>

                    <div className="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-3 border-t border-slate-100 pt-5 text-sm">
                        <Legend
                            type="available"
                            label={t(
                                "available"
                            )}
                        />

                        <Legend
                            type="low"
                            label={t(
                                "lowAvailability"
                            )}
                        />

                        <Legend
                            type="unavailable"
                            label={t(
                                "unavailable"
                            )}
                        />
                    </div>

                    {effectiveSelectedRange?.from &&
                        effectiveSelectedRange.to && (
                            <div className="mt-6 rounded-xl bg-slate-50 p-4 sm:p-5">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-900">
                                            {t(
                                                "selectedPeriod"
                                            )}
                                        </p>

                                        <p className="mt-1 text-sm text-slate-600">
                                            {effectiveSelectedRange.from.toLocaleDateString(
                                                "hu-HU"
                                            )}
                                            {
                                                " – "
                                            }
                                            {effectiveSelectedRange.to.toLocaleDateString(
                                                "hu-HU"
                                            )}
                                        </p>
                                    </div>

                                    <div>
                                        {fixedPeriodLoading ? (
                                            <p className="text-sm text-slate-500">
                                                {t(
                                                    "loading"
                                                )}
                                            </p>
                                        ) : fixedPeriodError ? (
                                            <p className="text-sm font-medium text-red-700">
                                                {t(
                                                    "error"
                                                )}
                                            </p>
                                        ) : selectedPeriodAvailability ? (
                                            selectedPeriodAvailability.available ? (
                                                <p className="text-sm font-medium text-emerald-700">
                                                    {t(
                                                        "availableQuantity",
                                                        {
                                                            count:
                                                                selectedPeriodAvailability.quantity,
                                                        }
                                                    )}
                                                </p>
                                            ) : (
                                                <p className="text-sm font-medium text-red-700">
                                                    {t(
                                                        "periodUnavailable"
                                                    )}
                                                </p>
                                            )
                                        ) : null}
                                    </div>
                                </div>
                            </div>
                        )}
                </>
            )}
        </section>
    );
}

type LegendProps = {
    type:
    | "available"
    | "low"
    | "unavailable";
    label: string;
};

function Legend({
    type,
    label,
}: LegendProps) {
    const indicatorClassNames = {
        available:
            "border-emerald-500 bg-emerald-500",
        low:
            "border-amber-500 bg-amber-500",
        unavailable:
            "border-slate-300 bg-slate-200",
    };

    return (
        <div className="flex items-center gap-2">
            <span
                className={[
                    "size-2.5 rounded-full border",
                    indicatorClassNames[
                    type
                    ],
                ].join(" ")}
            />

            <span className="text-slate-600">
                {label}
            </span>
        </div>
    );
}
