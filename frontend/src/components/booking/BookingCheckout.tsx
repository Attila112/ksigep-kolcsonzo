"use client";

import {
    useEffect,
    useMemo,
    useState,
} from "react";
import { useTranslations } from "next-intl";

import { useBookingCart } from "@/components/booking/BookingCartProvider";
import {
    calculateItemDepositTotal,
    calculateItemRentalTotal,
    calculateRentalDays,
} from "@/core/booking/bookingCalculations";
import { saveBookingSuccess } from "@/core/booking/bookingSuccessStorage";
import { useRouter } from "@/core/i18n/navigation";
import { checkBookingAvailability } from "@/services/bookingAvailabilityService";
import { createBookingAction } from "@/core/booking/bookingActions";

import type {
    BookingPickupType,
    CreateBookingRequest,
} from "@/types/booking";

type CheckoutFormState = {
    customerName: string;
    customerEmail: string;
    customerPhone: string;
    pickupType: BookingPickupType;
    plannedPickupAt: string;
    deliveryPostalCode: string;
    deliveryCity: string;
    deliveryStreet: string;
    deliveryHouseNumber: string;
    customerNote: string;
};

type FormErrors =
    Partial<
        Record<
            keyof CheckoutFormState,
            string
        >
    >;

const INITIAL_FORM: CheckoutFormState = {
    customerName: "",
    customerEmail: "",
    customerPhone: "",
    pickupType: "SELF_PICKUP",
    plannedPickupAt: "",
    deliveryPostalCode: "",
    deliveryCity: "",
    deliveryStreet: "",
    deliveryHouseNumber: "",
    customerNote: "",
};

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
    const [year, month, day] =
        value.split("-").map(Number);

    return new Intl.DateTimeFormat(
        "hu-HU"
    ).format(
        new Date(
            year,
            month - 1,
            day
        )
    );
}

export function BookingCheckout() {
    const t = useTranslations(
        "Booking.checkout"
    );

    const router = useRouter();

    const [
        bookingCreated,
        setBookingCreated,
    ] = useState(false);

    const {
        period,
        items,
        clearCart,
    } = useBookingCart();

    const [
        form,
        setForm,
    ] = useState<CheckoutFormState>(
        INITIAL_FORM
    );

    const [
        errors,
        setErrors,
    ] = useState<FormErrors>({});

    const [
        submitError,
        setSubmitError,
    ] = useState<string | null>(
        null
    );

    const [
        submitting,
        setSubmitting,
    ] = useState(false);

    const [
        cartChecked,
        setCartChecked,
    ] = useState(false);

    useEffect(() => {
        queueMicrotask(() => {
            setCartChecked(true);
        });
    }, []);

    useEffect(() => {
        if (
            !cartChecked ||
            bookingCreated
        ) {
            return;
        }

        if (
            !period ||
            items.length === 0
        ) {
            router.replace(
                "/booking"
            );
        }
    }, [
        cartChecked,
        bookingCreated,
        period,
        items.length,
        router,
    ]);

    const rentalDays =
        period
            ? calculateRentalDays(
                period
            )
            : 0;

    const rentalTotal =
        useMemo(
            () =>
                period
                    ? items.reduce(
                        (
                            total,
                            item
                        ) =>
                            total +
                            calculateItemRentalTotal(
                                item,
                                rentalDays
                            ),
                        0
                    )
                    : 0,
            [
                items,
                period,
                rentalDays,
            ]
        );

    const depositTotal =
        useMemo(
            () =>
                items.reduce(
                    (
                        total,
                        item
                    ) =>
                        total +
                        calculateItemDepositTotal(
                            item
                        ),
                    0
                ),
            [items]
        );

    const totalPayable =
        rentalTotal +
        depositTotal;

    function updateField<
        K extends keyof CheckoutFormState
    >(
        field: K,
        value: CheckoutFormState[K]
    ): void {
        setForm(
            (currentForm) => ({
                ...currentForm,
                [field]: value,
            })
        );

        setErrors(
            (currentErrors) => ({
                ...currentErrors,
                [field]: undefined,
            })
        );

        setSubmitError(null);
    }

    function handlePickupTypeChange(
        pickupType: BookingPickupType
    ): void {
        setForm(
            (currentForm) => ({
                ...currentForm,
                pickupType,

                plannedPickupAt:
                    pickupType ===
                        "SELF_PICKUP"
                        ? currentForm
                            .plannedPickupAt
                        : "",

                deliveryPostalCode:
                    pickupType ===
                        "DELIVERY"
                        ? currentForm
                            .deliveryPostalCode
                        : "",

                deliveryCity:
                    pickupType ===
                        "DELIVERY"
                        ? currentForm
                            .deliveryCity
                        : "",

                deliveryStreet:
                    pickupType ===
                        "DELIVERY"
                        ? currentForm
                            .deliveryStreet
                        : "",

                deliveryHouseNumber:
                    pickupType ===
                        "DELIVERY"
                        ? currentForm
                            .deliveryHouseNumber
                        : "",
            })
        );

        setErrors({});
        setSubmitError(null);
    }

    function validateForm(): boolean {
        const nextErrors:
            FormErrors = {};

        if (
            !form.customerName.trim()
        ) {
            nextErrors.customerName =
                t(
                    "validation.nameRequired"
                );
        }

        if (
            !form.customerEmail.trim()
        ) {
            nextErrors.customerEmail =
                t(
                    "validation.emailRequired"
                );
        } else {
            const emailPattern =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (
                !emailPattern.test(
                    form.customerEmail
                )
            ) {
                nextErrors.customerEmail =
                    t(
                        "validation.emailInvalid"
                    );
            }
        }

        if (
            !form.customerPhone.trim()
        ) {
            nextErrors.customerPhone =
                t(
                    "validation.phoneRequired"
                );
        }

        if (
            form.pickupType ===
            "SELF_PICKUP" &&
            !form.plannedPickupAt
        ) {
            nextErrors.plannedPickupAt =
                t(
                    "validation.pickupTimeRequired"
                );
        }

        if (
            form.pickupType ===
            "DELIVERY"
        ) {
            if (
                !form.deliveryPostalCode.trim()
            ) {
                nextErrors.deliveryPostalCode =
                    t(
                        "validation.postalCodeRequired"
                    );
            }

            if (
                !form.deliveryCity.trim()
            ) {
                nextErrors.deliveryCity =
                    t(
                        "validation.cityRequired"
                    );
            }

            if (
                !form.deliveryStreet.trim()
            ) {
                nextErrors.deliveryStreet =
                    t(
                        "validation.streetRequired"
                    );
            }

            if (
                !form.deliveryHouseNumber.trim()
            ) {
                nextErrors.deliveryHouseNumber =
                    t(
                        "validation.houseNumberRequired"
                    );
            }
        }

        setErrors(nextErrors);

        return (
            Object.keys(
                nextErrors
            ).length === 0
        );
    }

    async function handleSubmit(
        event:
            React.FormEvent<HTMLFormElement>
    ): Promise<void> {
        event.preventDefault();

        if (
            submitting ||
            !period ||
            items.length === 0
        ) {
            return;
        }

        setSubmitError(null);

        if (!validateForm()) {
            return;
        }

        setSubmitting(true);

        try {
            /*
             * Első védelmi vonal:
             * közvetlenül a booking létrehozása
             * előtt újra ellenőrizzük a teljes
             * kosár elérhetőségét.
             */
            const availability =
                await checkBookingAvailability(
                    {
                        start_date:
                            period.startDate,
                        end_date:
                            period.endDate,
                        items:
                            items.map(
                                (
                                    item
                                ) => ({
                                    product_id:
                                        item.productId,
                                })
                            ),
                    }
                );

            const availabilityByProduct =
                new Map(
                    availability.items.map(
                        (item) => [
                            item.product_id,
                            item,
                        ]
                    )
                );

            const unavailable =
                items.some(
                    (item) => {
                        const result =
                            availabilityByProduct.get(
                                item.productId
                            );

                        return (
                            !result ||
                            !result.available ||
                            item.quantity >
                            result.available_quantity
                        );
                    }
                );

            if (unavailable) {
                setSubmitError(
                    t(
                        "errors.availabilityChanged"
                    )
                );

                return;
            }

            const request:
                CreateBookingRequest =
            {
                customer_name:
                    form.customerName.trim(),

                customer_email:
                    form.customerEmail
                        .trim()
                        .toLowerCase(),

                customer_phone:
                    form.customerPhone.trim(),

                start_date:
                    period.startDate,

                end_date:
                    period.endDate,

                pickup_type:
                    form.pickupType,

                planned_pickup_at:
                    form.pickupType ===
                        "SELF_PICKUP"
                        ? form.plannedPickupAt
                        : null,

                delivery_postal_code:
                    form.pickupType ===
                        "DELIVERY"
                        ? form.deliveryPostalCode.trim()
                        : null,

                delivery_city:
                    form.pickupType ===
                        "DELIVERY"
                        ? form.deliveryCity.trim()
                        : null,

                delivery_street:
                    form.pickupType ===
                        "DELIVERY"
                        ? form.deliveryStreet.trim()
                        : null,

                delivery_house_number:
                    form.pickupType ===
                        "DELIVERY"
                        ? form.deliveryHouseNumber.trim()
                        : null,

                customer_note:
                    form.customerNote.trim()
                        ? form.customerNote.trim()
                        : null,

                items:
                    items.map(
                        (item) => ({
                            product_id:
                                item.productId,
                            quantity:
                                item.quantity,
                        })
                    ),
            };

            /*
             * Második, végső védelmi vonal:
             * a backend a BookingService-ben
             * ismét availability-t ellenőriz és
             * az árakat is újraszámolja.
             */
            const result =
                await createBookingAction(
                    request
                );

            if (!result.success) {
                setSubmitError(
                    result.message ===
                        "UNKNOWN_ERROR"
                        ? t(
                            "errors.submit"
                        )
                        : result.message
                );

                return;
            }

            saveBookingSuccess(
                result.data.booking
            );
            setBookingCreated(true);
            
            clearCart();

            router.replace(
                "/booking/success"
            );
        } catch {
            setSubmitError(
                t(
                    "errors.submit"
                )
            );
        } finally {
            setSubmitting(false);
        }
    }

    if (
        !cartChecked ||
        !period ||
        items.length === 0
    ) {
        return (
            <div className="py-16 text-center text-sm text-slate-500">
                {t("loading")}
            </div>
        );
    }

    return (
        <div>
            <div className="mb-8">
                <h1 className="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {t("title")}
                </h1>

                <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                    {t(
                        "description"
                    )}
                </p>
            </div>

            <form
                onSubmit={
                    handleSubmit
                }
                noValidate
                className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start"
            >
                <div className="space-y-6">
                    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                        <SectionHeader
                            title={t(
                                "customer.title"
                            )}
                            description={t(
                                "customer.description"
                            )}
                        />

                        <div className="mt-6 grid gap-5">
                            <FormField
                                label={t(
                                    "customer.name"
                                )}
                                htmlFor="customerName"
                                error={
                                    errors.customerName
                                }
                                required
                            >
                                <input
                                    id="customerName"
                                    type="text"
                                    value={
                                        form.customerName
                                    }
                                    onChange={(
                                        event
                                    ) =>
                                        updateField(
                                            "customerName",
                                            event
                                                .target
                                                .value
                                        )
                                    }
                                    autoComplete="name"
                                    className={
                                        inputClassName
                                    }
                                />
                            </FormField>

                            <FormField
                                label={t(
                                    "customer.email"
                                )}
                                htmlFor="customerEmail"
                                error={
                                    errors.customerEmail
                                }
                                required
                            >
                                <input
                                    id="customerEmail"
                                    type="email"
                                    value={
                                        form.customerEmail
                                    }
                                    onChange={(
                                        event
                                    ) =>
                                        updateField(
                                            "customerEmail",
                                            event
                                                .target
                                                .value
                                        )
                                    }
                                    autoComplete="email"
                                    className={
                                        inputClassName
                                    }
                                />
                            </FormField>

                            <FormField
                                label={t(
                                    "customer.phone"
                                )}
                                htmlFor="customerPhone"
                                error={
                                    errors.customerPhone
                                }
                                required
                            >
                                <input
                                    id="customerPhone"
                                    type="tel"
                                    value={
                                        form.customerPhone
                                    }
                                    onChange={(
                                        event
                                    ) =>
                                        updateField(
                                            "customerPhone",
                                            event
                                                .target
                                                .value
                                        )
                                    }
                                    autoComplete="tel"
                                    className={
                                        inputClassName
                                    }
                                />
                            </FormField>
                        </div>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                        <SectionHeader
                            title={t(
                                "pickup.title"
                            )}
                            description={t(
                                "pickup.description"
                            )}
                        />

                        <div className="mt-6 grid gap-3 sm:grid-cols-2">
                            <PickupOption
                                checked={
                                    form.pickupType ===
                                    "SELF_PICKUP"
                                }
                                title={t(
                                    "pickup.selfPickup.title"
                                )}
                                description={t(
                                    "pickup.selfPickup.description"
                                )}
                                onChange={() =>
                                    handlePickupTypeChange(
                                        "SELF_PICKUP"
                                    )
                                }
                            />

                            <PickupOption
                                checked={
                                    form.pickupType ===
                                    "DELIVERY"
                                }
                                title={t(
                                    "pickup.delivery.title"
                                )}
                                description={t(
                                    "pickup.delivery.description"
                                )}
                                onChange={() =>
                                    handlePickupTypeChange(
                                        "DELIVERY"
                                    )
                                }
                            />
                        </div>

                        {form.pickupType ===
                            "SELF_PICKUP" ? (
                            <div className="mt-6 border-t border-slate-100 pt-6">
                                <FormField
                                    label={t(
                                        "pickup.selfPickup.plannedPickupAt"
                                    )}
                                    htmlFor="plannedPickupAt"
                                    error={
                                        errors.plannedPickupAt
                                    }
                                    required
                                >
                                    <input
                                        id="plannedPickupAt"
                                        type="datetime-local"
                                        value={
                                            form.plannedPickupAt
                                        }
                                        onChange={(
                                            event
                                        ) =>
                                            updateField(
                                                "plannedPickupAt",
                                                event
                                                    .target
                                                    .value
                                            )
                                        }
                                        className={
                                            inputClassName
                                        }
                                    />
                                </FormField>
                            </div>
                        ) : (
                            <div className="mt-6 border-t border-slate-100 pt-6">
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <FormField
                                        label={t(
                                            "pickup.delivery.postalCode"
                                        )}
                                        htmlFor="deliveryPostalCode"
                                        error={
                                            errors.deliveryPostalCode
                                        }
                                        required
                                    >
                                        <input
                                            id="deliveryPostalCode"
                                            type="text"
                                            value={
                                                form.deliveryPostalCode
                                            }
                                            onChange={(
                                                event
                                            ) =>
                                                updateField(
                                                    "deliveryPostalCode",
                                                    event
                                                        .target
                                                        .value
                                                )
                                            }
                                            autoComplete="postal-code"
                                            className={
                                                inputClassName
                                            }
                                        />
                                    </FormField>

                                    <FormField
                                        label={t(
                                            "pickup.delivery.city"
                                        )}
                                        htmlFor="deliveryCity"
                                        error={
                                            errors.deliveryCity
                                        }
                                        required
                                    >
                                        <input
                                            id="deliveryCity"
                                            type="text"
                                            value={
                                                form.deliveryCity
                                            }
                                            onChange={(
                                                event
                                            ) =>
                                                updateField(
                                                    "deliveryCity",
                                                    event
                                                        .target
                                                        .value
                                                )
                                            }
                                            autoComplete="address-level2"
                                            className={
                                                inputClassName
                                            }
                                        />
                                    </FormField>

                                    <FormField
                                        label={t(
                                            "pickup.delivery.street"
                                        )}
                                        htmlFor="deliveryStreet"
                                        error={
                                            errors.deliveryStreet
                                        }
                                        required
                                    >
                                        <input
                                            id="deliveryStreet"
                                            type="text"
                                            value={
                                                form.deliveryStreet
                                            }
                                            onChange={(
                                                event
                                            ) =>
                                                updateField(
                                                    "deliveryStreet",
                                                    event
                                                        .target
                                                        .value
                                                )
                                            }
                                            autoComplete="address-line1"
                                            className={
                                                inputClassName
                                            }
                                        />
                                    </FormField>

                                    <FormField
                                        label={t(
                                            "pickup.delivery.houseNumber"
                                        )}
                                        htmlFor="deliveryHouseNumber"
                                        error={
                                            errors.deliveryHouseNumber
                                        }
                                        required
                                    >
                                        <input
                                            id="deliveryHouseNumber"
                                            type="text"
                                            value={
                                                form.deliveryHouseNumber
                                            }
                                            onChange={(
                                                event
                                            ) =>
                                                updateField(
                                                    "deliveryHouseNumber",
                                                    event
                                                        .target
                                                        .value
                                                )
                                            }
                                            className={
                                                inputClassName
                                            }
                                        />
                                    </FormField>
                                </div>
                            </div>
                        )}
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                        <SectionHeader
                            title={t(
                                "note.title"
                            )}
                            description={t(
                                "note.description"
                            )}
                        />

                        <div className="mt-6">
                            <textarea
                                id="customerNote"
                                value={
                                    form.customerNote
                                }
                                onChange={(
                                    event
                                ) =>
                                    updateField(
                                        "customerNote",
                                        event
                                            .target
                                            .value
                                    )
                                }
                                rows={5}
                                maxLength={
                                    5000
                                }
                                className={`${inputClassName} resize-y`}
                            />
                        </div>
                    </section>
                </div>

                <aside className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6 lg:sticky lg:top-6">
                    <h2 className="text-lg font-semibold text-slate-900">
                        {t(
                            "summary.title"
                        )}
                    </h2>

                    <div className="mt-5 border-b border-slate-100 pb-5">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            {t(
                                "summary.period"
                            )}
                        </p>

                        <p className="mt-1 text-sm font-medium text-slate-900">
                            {formatDate(
                                period.startDate
                            )}
                            {" – "}
                            {formatDate(
                                period.endDate
                            )}
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            {t(
                                "summary.days",
                                {
                                    count:
                                        rentalDays,
                                }
                            )}
                        </p>
                    </div>

                    <div className="divide-y divide-slate-100">
                        {items.map(
                            (item) => (
                                <div
                                    key={
                                        item.productId
                                    }
                                    className="py-4"
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <p className="text-sm font-medium text-slate-900">
                                                {
                                                    item.productName
                                                }
                                            </p>

                                            <p className="mt-1 text-xs text-slate-500">
                                                {t(
                                                    "summary.quantity",
                                                    {
                                                        count:
                                                            item.quantity,
                                                    }
                                                )}
                                            </p>
                                        </div>

                                        <p className="shrink-0 text-sm font-medium text-slate-900">
                                            {formatPrice(
                                                calculateItemRentalTotal(
                                                    item,
                                                    rentalDays
                                                )
                                            )}
                                        </p>
                                    </div>
                                </div>
                            )
                        )}
                    </div>

                    <div className="space-y-3 border-t border-slate-200 pt-5">
                        <SummaryRow
                            label={t(
                                "summary.rentalTotal"
                            )}
                            value={formatPrice(
                                rentalTotal
                            )}
                        />

                        <SummaryRow
                            label={t(
                                "summary.depositTotal"
                            )}
                            value={formatPrice(
                                depositTotal
                            )}
                        />

                        <div className="flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
                            <span className="font-semibold text-slate-900">
                                {t(
                                    "summary.total"
                                )}
                            </span>

                            <span className="text-lg font-bold text-slate-900">
                                {formatPrice(
                                    totalPayable
                                )}
                            </span>
                        </div>
                    </div>

                    {submitError && (
                        <div
                            role="alert"
                            className="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm leading-5 text-red-700"
                        >
                            {submitError}
                        </div>
                    )}

                    <button
                        type="submit"
                        disabled={
                            submitting
                        }
                        className="mt-6 w-full rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {submitting
                            ? t(
                                "submitting"
                            )
                            : t(
                                "submit"
                            )}
                    </button>

                    <button
                        type="button"
                        disabled={
                            submitting
                        }
                        onClick={() =>
                            router.push(
                                "/booking"
                            )
                        }
                        className="mt-3 w-full rounded-lg border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {t(
                            "backToCart"
                        )}
                    </button>
                </aside>
            </form>
        </div>
    );
}

const inputClassName =
    "w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100";

type SectionHeaderProps = {
    title: string;
    description: string;
};

function SectionHeader({
    title,
    description,
}: SectionHeaderProps) {
    return (
        <div className="border-b border-slate-100 pb-5">
            <h2 className="text-lg font-semibold text-slate-900">
                {title}
            </h2>

            <p className="mt-1 text-sm leading-6 text-slate-500">
                {description}
            </p>
        </div>
    );
}

type FormFieldProps = {
    label: string;
    htmlFor: string;
    required?: boolean;
    error?: string;
    children: React.ReactNode;
};

function FormField({
    label,
    htmlFor,
    required = false,
    error,
    children,
}: FormFieldProps) {
    return (
        <div>
            <label
                htmlFor={htmlFor}
                className="mb-2 block text-sm font-medium text-slate-700"
            >
                {label}

                {required && (
                    <span
                        className="ml-1 text-red-500"
                        aria-hidden="true"
                    >
                        *
                    </span>
                )}
            </label>

            {children}

            {error && (
                <p className="mt-1.5 text-sm text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}

type PickupOptionProps = {
    checked: boolean;
    title: string;
    description: string;
    onChange: () => void;
};

function PickupOption({
    checked,
    title,
    description,
    onChange,
}: PickupOptionProps) {
    return (
        <label
            className={[
                "cursor-pointer rounded-xl border p-4 transition",
                checked
                    ? "border-blue-500 bg-blue-50"
                    : "border-slate-200 bg-white hover:border-slate-300",
            ].join(" ")}
        >
            <div className="flex items-start gap-3">
                <input
                    type="radio"
                    name="pickupType"
                    checked={checked}
                    onChange={
                        onChange
                    }
                    className="mt-1 size-4 accent-blue-600"
                />

                <div>
                    <p className="text-sm font-semibold text-slate-900">
                        {title}
                    </p>

                    <p className="mt-1 text-sm leading-5 text-slate-500">
                        {description}
                    </p>
                </div>
            </div>
        </label>
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