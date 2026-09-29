"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { useRouter } from "@/core/i18n/navigation";

import { useBookingCart } from "@/components/booking/BookingCartProvider";
import {
    calculateItemDepositTotal,
    calculateItemRentalTotal,
    calculateRentalDays,
} from "@/core/booking/bookingCalculations";
import { checkBookingAvailability } from "@/services/bookingAvailabilityService";
import { BookingPeriodEditor } from "@/components/booking/BookingPeriodEditor";

import type {
    BookingAvailabilityItem,
    BookingAvailabilityResponse,
} from "@/types/bookingAvailability";
import type { BookingCartItem } from "@/types/bookingCart";

type ProductAvailabilityMap = Record<
    number,
    BookingAvailabilityItem
>;

function formatPrice(value: number): string {
    return new Intl.NumberFormat("hu-HU", {
        style: "currency",
        currency: "HUF",
        maximumFractionDigits: 0,
    }).format(value);
}

function formatDate(value: string): string {
    const [year, month, day] = value
        .split("-")
        .map(Number);

    return new Intl.DateTimeFormat(
        "hu-HU"
    ).format(
        new Date(year, month - 1, day)
    );
}

function createAvailabilityMap(
    response: BookingAvailabilityResponse
): ProductAvailabilityMap {
    return Object.fromEntries(
        response.items.map((item) => [
            item.product_id,
            item,
        ])
    ) as ProductAvailabilityMap;
}

export function BookingCartSummary() {
    const t = useTranslations(
        "Booking.cart"
    );

    const router = useRouter();

    const {
        period,
        items,
        updateQuantity,
        updatePeriod,
        removeItem,
        clearCart,
    } = useBookingCart();

    const [
        availabilityByProduct,
        setAvailabilityByProduct,
    ] = useState<ProductAvailabilityMap>(
        {}
    );

    const [
        checkingProductId,
        setCheckingProductId,
    ] = useState<number | null>(null);

    const [
        checkingCart,
        setCheckingCart,
    ] = useState(false);

    const [
        availabilityError,
        setAvailabilityError,
    ] = useState(false);

    const [
        unavailableProductIds,
        setUnavailableProductIds,
    ] = useState<number[]>([]);

    if (!period || items.length === 0) {
        return (
            <div className="rounded-lg border border-slate-200 bg-white p-5">
                <h1 className="text-2xl font-semibold text-slate-900">
                    {t("title")}
                </h1>

                <p className="mt-4 text-slate-600">
                    {t("empty")}
                </p>
            </div>
        );
    }

    const rentalDays =
        calculateRentalDays(period);

    const rentalTotal = items.reduce(
        (total, item) =>
            total +
            calculateItemRentalTotal(
                item,
                rentalDays
            ),
        0
    );

    const depositTotal = items.reduce(
        (total, item) =>
            total +
            calculateItemDepositTotal(
                item
            ),
        0
    );

    const totalPayable =
        rentalTotal + depositTotal;

    async function loadCurrentAvailability(): Promise<
        ProductAvailabilityMap | null
    > {
        if (!period) {
            return null;
        }
        try {
            const response =
                await checkBookingAvailability({
                    start_date:
                        period.startDate,
                    end_date:
                        period.endDate,
                    items: items.map(
                        (item) => ({
                            product_id:
                                item.productId,
                        })
                    ),
                });

            const availabilityMap =
                createAvailabilityMap(
                    response
                );

            setAvailabilityByProduct(
                availabilityMap
            );

            setAvailabilityError(false);

            return availabilityMap;
        } catch {
            setAvailabilityError(true);

            return null;
        }
    }

    async function handleIncrease(
        item: BookingCartItem
    ): Promise<void> {
        if (
            checkingProductId !== null ||
            checkingCart
        ) {
            return;
        }

        setCheckingProductId(
            item.productId
        );

        setUnavailableProductIds([]);

        try {
            const availabilityMap =
                await loadCurrentAvailability();

            if (!availabilityMap) {
                return;
            }

            const availability =
                availabilityMap[
                item.productId
                ];

            if (!availability) {
                setAvailabilityError(true);
                return;
            }

            const nextQuantity =
                item.quantity + 1;

            if (
                !availability.available ||
                nextQuantity >
                availability.available_quantity
            ) {
                setUnavailableProductIds([
                    item.productId,
                ]);

                return;
            }

            updateQuantity(
                item.productId,
                nextQuantity
            );
        } finally {
            setCheckingProductId(null);
        }
    }

    function handleDecrease(
        item: BookingCartItem
    ): void {
        if (item.quantity <= 1) {
            return;
        }

        updateQuantity(
            item.productId,
            item.quantity - 1
        );

        setUnavailableProductIds(
            (current) =>
                current.filter(
                    (productId) =>
                        productId !==
                        item.productId
                )
        );
    }

    function handleRemove(
        productId: number
    ): void {
        removeItem(productId);

        setUnavailableProductIds(
            (current) =>
                current.filter(
                    (currentProductId) =>
                        currentProductId !==
                        productId
                )
        );

        setAvailabilityByProduct(
            (current) => {
                const next = {
                    ...current,
                };

                delete next[productId];

                return next;
            }
        );
    }

    async function handleContinue(): Promise<void> {
        if (
            checkingCart ||
            checkingProductId !== null
        ) {
            return;
        }

        setCheckingCart(true);
        setUnavailableProductIds([]);

        try {
            const availabilityMap =
                await loadCurrentAvailability();

            if (!availabilityMap) {
                return;
            }

            const unavailableIds =
                items
                    .filter((item) => {
                        const availability =
                            availabilityMap[
                            item.productId
                            ];

                        if (!availability) {
                            return true;
                        }

                        return (
                            !availability.available ||
                            item.quantity >
                            availability.available_quantity
                        );
                    })
                    .map(
                        (item) =>
                            item.productId
                    );

            if (
                unavailableIds.length > 0
            ) {
                setUnavailableProductIds(
                    unavailableIds
                );

                return;
            }

            router.push("/booking/checkout");
        } finally {
            setCheckingCart(false);
        }
    }

    const interactionLocked =
        checkingCart ||
        checkingProductId !== null;

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-semibold text-slate-900">
                    {t("title")}
                </h1>

                <p className="mt-2 text-sm text-slate-600">
                    {t("period", {
                        startDate: formatDate(
                            period.startDate
                        ),
                        endDate: formatDate(
                            period.endDate
                        ),
                        days: rentalDays,
                    })}
                </p>
                <BookingPeriodEditor
                    period={period}
                    items={items}
                    disabled={interactionLocked}
                    onPeriodChange={(
                        nextPeriod
                    ) => {
                        updatePeriod(
                            nextPeriod
                        );

                        /*
                         * Az előző időszak availability
                         * eredményei az új időszakra már
                         * nem érvényesek.
                         */
                        setAvailabilityByProduct(
                            {}
                        );

                        setUnavailableProductIds(
                            []
                        );

                        setAvailabilityError(
                            false
                        );
                    }}
                />
            </div>

            <div className="space-y-3">
                {items.map((item) => {
                    const itemRentalTotal =
                        calculateItemRentalTotal(
                            item,
                            rentalDays
                        );

                    const itemDepositTotal =
                        calculateItemDepositTotal(
                            item
                        );

                    const availability =
                        availabilityByProduct[
                        item.productId
                        ];

                    const isChecking =
                        checkingProductId ===
                        item.productId;

                    const isUnavailable =
                        unavailableProductIds.includes(
                            item.productId
                        );

                    const canDecrease =
                        item.quantity > 1 &&
                        !interactionLocked;

                    const knownMaximum =
                        availability?.available_quantity ??
                        item.availableQuantity;

                    const showQuantityControls =
                        knownMaximum > 1;

                    const reachedKnownMaximum =
                        item.quantity >=
                        knownMaximum;

                    const canIncrease =
                        !interactionLocked &&
                        !reachedKnownMaximum;

                    return (
                        <div
                            key={
                                item.productId
                            }
                            className="rounded-lg border border-slate-200 bg-white p-4 sm:p-5"
                        >
                            <div className="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 className="font-semibold text-slate-900">
                                        {
                                            item.productName
                                        }
                                    </h2>

                                    <p className="mt-2 text-sm text-slate-600">
                                        {t(
                                            "pricePerDay",
                                            {
                                                price: formatPrice(
                                                    item.pricePerDay
                                                ),
                                            }
                                        )}
                                    </p>

                                    <p className="mt-1 text-sm text-slate-600">
                                        {t(
                                            "itemDeposit",
                                            {
                                                price: formatPrice(
                                                    itemDepositTotal
                                                ),
                                            }
                                        )}
                                    </p>

                                    {showQuantityControls && (
                                        <div className="mt-4">
                                            <p className="text-sm font-medium text-slate-900">
                                                {t("quantity")}
                                            </p>

                                            <div className="mt-2 flex items-center gap-3">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleDecrease(
                                                            item
                                                        )
                                                    }
                                                    disabled={
                                                        !canDecrease
                                                    }
                                                    aria-label={t(
                                                        "decreaseQuantity"
                                                    )}
                                                    className="flex size-9 items-center justify-center rounded-lg border border-slate-300 font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    −
                                                </button>

                                                <span className="min-w-8 text-center font-semibold text-slate-900">
                                                    {item.quantity}
                                                </span>

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        void handleIncrease(
                                                            item
                                                        )
                                                    }
                                                    disabled={
                                                        !canIncrease
                                                    }
                                                    aria-label={t(
                                                        "increaseQuantity"
                                                    )}
                                                    className="flex size-9 items-center justify-center rounded-lg border border-slate-300 font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    +
                                                </button>
                                            </div>

                                            {isChecking && (
                                                <p className="mt-2 text-xs text-slate-500">
                                                    {t(
                                                        "checkingAvailability"
                                                    )}
                                                </p>
                                            )}

                                            {availability &&
                                                !isChecking && (
                                                    <p className="mt-2 text-xs text-slate-500">
                                                        {t(
                                                            "maximumAvailable",
                                                            {
                                                                count:
                                                                    availability.available_quantity,
                                                            }
                                                        )}
                                                    </p>
                                                )}
                                        </div>
                                    )}

                                    {isUnavailable && (
                                        <p className="mt-3 text-xs font-medium text-red-600">
                                            {t(
                                                "quantityUnavailable"
                                            )}
                                        </p>
                                    )}
                                </div>

                                <div className="sm:text-right">
                                    <p className="font-semibold text-slate-900">
                                        {formatPrice(
                                            itemRentalTotal
                                        )}
                                    </p>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            handleRemove(
                                                item.productId
                                            )
                                        }
                                        disabled={
                                            interactionLocked
                                        }
                                        className="mt-3 text-sm font-medium text-red-600 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {t(
                                            "remove"
                                        )}
                                    </button>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>

            <div className="rounded-lg border border-slate-200 bg-white p-4 sm:p-5">
                <div className="space-y-3">
                    <SummaryRow
                        label={t(
                            "rentalTotal"
                        )}
                        value={formatPrice(
                            rentalTotal
                        )}
                    />

                    <SummaryRow
                        label={t(
                            "depositTotal"
                        )}
                        value={formatPrice(
                            depositTotal
                        )}
                    />

                    <div className="border-t border-slate-200 pt-3">
                        <SummaryRow
                            label={t(
                                "totalPayable"
                            )}
                            value={formatPrice(
                                totalPayable
                            )}
                            emphasized
                        />
                    </div>
                </div>

                {availabilityError && (
                    <p className="mt-4 text-sm text-red-600">
                        {t(
                            "availabilityCheckError"
                        )}
                    </p>
                )}

                {unavailableProductIds.length >
                    0 && (
                        <p className="mt-4 text-sm text-red-600">
                            {t(
                                "cartUnavailable"
                            )}
                        </p>
                    )}

                <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <button
                        type="button"
                        onClick={clearCart}
                        disabled={
                            interactionLocked
                        }
                        className="rounded-lg border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {t("clear")}
                    </button>

                    <button
                        type="button"
                        onClick={() =>
                            void handleContinue()
                        }
                        disabled={
                            interactionLocked
                        }
                        className="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {checkingCart
                            ? t(
                                "checkingAvailability"
                            )
                            : t(
                                "continue"
                            )}
                    </button>
                </div>
            </div>
        </div>
    );
}

type SummaryRowProps = {
    label: string;
    value: string;
    emphasized?: boolean;
};

function SummaryRow({
    label,
    value,
    emphasized = false,
}: SummaryRowProps) {
    return (
        <div className="flex items-center justify-between gap-4">
            <span
                className={
                    emphasized
                        ? "font-semibold text-slate-900"
                        : "text-sm text-slate-600"
                }
            >
                {label}
            </span>

            <span
                className={
                    emphasized
                        ? "text-lg font-semibold text-slate-900"
                        : "text-sm font-medium text-slate-900"
                }
            >
                {value}
            </span>
        </div>
    );
}