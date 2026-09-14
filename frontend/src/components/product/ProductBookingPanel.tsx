"use client";

import { useCallback, useState } from "react";
import { useTranslations } from "next-intl";

import { ProductAvailabilityCalendar } from "@/components/product/ProductAvailabilityCalendar";
import { useBookingCart } from "@/components/booking/BookingCartProvider";

import type { ProductBookingSelection } from "@/types/productBooking";

type ProductBookingPanelProps = {
    product: {
        id: number;
        name: string;
        price_per_day: number;
        deposit: number;
    };
};

export function ProductBookingPanel({
    product,
}: ProductBookingPanelProps) {
    const t = useTranslations(
        "Product.details.booking"
    );

    const {
        period,
        addItem,
        hasProduct,
    } = useBookingCart();

    const [selection, setSelection] =
        useState<ProductBookingSelection | null>(
            null
        );

    const [quantity, setQuantity] =
        useState(1);

    const [message, setMessage] =
        useState<string | null>(null);

    const [error, setError] =
        useState<string | null>(null);

    const handleSelectionChange = useCallback(
        (
            newSelection: ProductBookingSelection | null
        ) => {
            setSelection(newSelection);

            if (!newSelection) {
                setQuantity(1);
                return;
            }

            setQuantity((currentQuantity) =>
                Math.min(
                    currentQuantity,
                    Math.max(
                        1,
                        newSelection.availableQuantity
                    )
                )
            );
        },
        []
    );

    const productAlreadyInCart =
        hasProduct(product.id);

    const cartPeriodMatchesSelection =
        !period ||
        !selection ||
        (
            period.startDate ===
            selection.startDate &&
            period.endDate ===
            selection.endDate
        );

    function decreaseQuantity() {
        setQuantity((currentQuantity) =>
            Math.max(
                1,
                currentQuantity - 1
            )
        );
    }

    function increaseQuantity() {
        if (!selection) {
            return;
        }

        setQuantity((currentQuantity) =>
            Math.min(
                selection.availableQuantity,
                currentQuantity + 1
            )
        );
    }

    function handleAddToBooking() {
        setMessage(null);
        setError(null);

        if (!selection) {
            return;
        }

        if (!cartPeriodMatchesSelection) {
            setError(
                t("differentPeriodError")
            );
            return;
        }

        try {
            addItem({
                period: {
                    startDate:
                        selection.startDate,
                    endDate:
                        selection.endDate,
                },
                item: {
                    productId: product.id,
                    productName:
                        product.name,
                    pricePerDay:
                        product.price_per_day,
                    deposit:
                        product.deposit,
                    quantity,
                },
            });

            setMessage(
                productAlreadyInCart
                    ? t("updated")
                    : t("added")
            );
        } catch {
            setError(
                t("differentPeriodError")
            );
        }
    }

    return (
        <div>
            <ProductAvailabilityCalendar
                productId={product.id}
                onSelectionChange={
                    handleSelectionChange
                }
            />

            {selection && (
                <div className="mx-auto mt-6 w-full max-w-5xl">
                    <div className="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                        {selection.availableQuantity >
                            1 && (
                                <div>
                                    <p className="text-sm font-medium text-slate-900">
                                        {t(
                                            "quantity"
                                        )}
                                    </p>

                                    <div className="mt-3 flex items-center gap-3">
                                        <button
                                            type="button"
                                            onClick={
                                                decreaseQuantity
                                            }
                                            disabled={
                                                quantity <=
                                                1
                                            }
                                            aria-label={t(
                                                "decreaseQuantity"
                                            )}
                                            className="flex size-10 items-center justify-center rounded-lg border border-slate-300 text-lg font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                                        >
                                            −
                                        </button>

                                        <span className="min-w-8 text-center text-base font-semibold text-slate-900">
                                            {
                                                quantity
                                            }
                                        </span>

                                        <button
                                            type="button"
                                            onClick={
                                                increaseQuantity
                                            }
                                            disabled={
                                                quantity >=
                                                selection.availableQuantity
                                            }
                                            aria-label={t(
                                                "increaseQuantity"
                                            )}
                                            className="flex size-10 items-center justify-center rounded-lg border border-slate-300 text-lg font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <p className="mt-2 text-sm text-slate-500">
                                        {t(
                                            "maximumAvailable",
                                            {
                                                count: selection.availableQuantity,
                                            }
                                        )}
                                    </p>
                                </div>
                            )}

                        {!cartPeriodMatchesSelection && (
                            <div className="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                {t(
                                    "differentPeriodWarning",
                                    {
                                        startDate:
                                            period?.startDate ??
                                            "",
                                        endDate:
                                            period?.endDate ??
                                            "",
                                    }
                                )}
                            </div>
                        )}

                        {message && (
                            <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                                {message}
                            </div>
                        )}

                        {error && (
                            <div className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {error}
                            </div>
                        )}

                        <button
                            type="button"
                            onClick={
                                handleAddToBooking
                            }
                            disabled={
                                !cartPeriodMatchesSelection
                            }
                            className="mt-5 w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            {productAlreadyInCart
                                ? t(
                                    "updateBooking"
                                )
                                : t(
                                    "addToBooking"
                                )}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}