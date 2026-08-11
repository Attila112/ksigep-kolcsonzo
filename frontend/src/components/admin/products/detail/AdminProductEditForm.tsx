"use client";

import { useState } from "react";
import { useTranslations } from "next-intl"
import { ApiError } from "@/core/api/api";

import type { AdminBatterySystemLookupItem, AdminCategoryLookupItem, } from "@/types/adminLookup";
import type { AdminProductDetail } from "@/types/adminProduct";
import { updateAdminProduct, type UpdateAdminProductData, } from "@/core/admin/productActions";

type AdminProductEditFormProps = {
    product: AdminProductDetail;
    categories: AdminCategoryLookupItem[];
    batterySystems: AdminBatterySystemLookupItem[];
    onCancel: () => void;
    onSaved: () => void;
};

export function AdminProductEditForm({
    product,
    categories,
    batterySystems,
    onCancel,
    onSaved,
}: AdminProductEditFormProps) {
    const t = useTranslations("Admin");

    const [fieldErrors, setFieldErrors] = useState<
        Record<string, string[]>
    >({});

    const [name, setName] = useState(product.name);
    const [description, setDescription] = useState(product.description);
    const [categoryId, setCategoryId] = useState(
        String(product.category.id)
    );
    const [pricePerDay, setPricePerDay] = useState(
        product.price_per_day
    );
    const [deposit, setDeposit] = useState(product.deposit);
    const [active, setActive] = useState(product.active);

    const [batterySystemId, setBatterySystemId] = useState(
        product.battery_system
            ? String(product.battery_system.id)
            : ""
    );

    const [requiredBatteries, setRequiredBatteries] = useState(
        String(product.required_batteries)
    );

    const [requiredChargers, setRequiredChargers] = useState(
        String(product.required_chargers)
    );

    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const hasBatterySystem = batterySystemId !== "";

    async function handleSubmit(
        event: React.FormEvent<HTMLFormElement>
    ) {
        event.preventDefault();

        setSaving(true);
        setError(null);
        setFieldErrors({});

        const data: UpdateAdminProductData = {
            category_id: Number(categoryId),
            name: name.trim(),
            description: description.trim(),
            price_per_day: Number(pricePerDay),
            deposit: Number(deposit),
            active,

            battery_system_id: hasBatterySystem
                ? Number(batterySystemId)
                : null,

            required_batteries: hasBatterySystem
                ? Number(requiredBatteries)
                : 0,

            required_chargers: hasBatterySystem
                ? Number(requiredChargers)
                : 0,
        };

        try {
            await updateAdminProduct(
                product.id,
                data
            );

            onSaved();
        } catch (error) {
            if (error instanceof ApiError) {
                if (error.status === 422 && error.errors) {
                    setFieldErrors(error.errors);

                    return;
                }

                setError(error.message);

                return;
            }

            setError(t("products.edit.saveError"));
        } finally {
            setSaving(false);
        }
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-6"
        >
            <div className="grid gap-5 md:grid-cols-2">
                <div className="md:col-span-2">
                    <label
                        htmlFor="product-name"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("products.edit.fields.name")}
                    </label>

                    <input
                        id="product-name"
                        type="text"
                        value={name}
                        onChange={(event) =>
                            setName(event.target.value)
                        }
                        required
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>
                {fieldErrors.name?.[0] && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {fieldErrors.name[0]}
                    </p>
                )}

                <div className="md:col-span-2">
                    <label
                        htmlFor="product-description"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("products.edit.fields.description")}
                    </label>

                    <textarea
                        id="product-description"
                        value={description}
                        onChange={(event) =>
                            setDescription(event.target.value)
                        }
                        required
                        rows={5}
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>
                {fieldErrors.description?.[0] && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {fieldErrors.description[0]}
                    </p>
                )}

                <div>
                    <label
                        htmlFor="product-category"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("products.edit.fields.category")}
                    </label>

                    <select
                        id="product-category"
                        value={categoryId}
                        onChange={(event) =>
                            setCategoryId(event.target.value)
                        }
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                        {categories.map((category) => (
                            <option
                                key={category.id}
                                value={category.id}
                            >
                                {category.name}
                                {!category.active
                                    ? ` (${t("status.inactive")})`
                                    : ""}
                            </option>
                        ))}
                    </select>
                    {fieldErrors.category_id?.[0] && (
                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                            {fieldErrors.category_id[0]}
                        </p>
                    )}
                </div>

                <div className="flex items-end">
                    <label className="flex min-h-10 items-center gap-3">
                        <input
                            type="checkbox"
                            checked={active}
                            onChange={(event) =>
                                setActive(event.target.checked)
                            }
                            className="h-4 w-4"
                        />

                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {t("products.edit.fields.active")}
                        </span>
                    </label>
                </div>

                <div>
                    <label
                        htmlFor="product-price"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("products.edit.fields.dailyPrice")}
                    </label>

                    <input
                        id="product-price"
                        type="number"
                        min="0"
                        step="1"
                        value={pricePerDay}
                        onChange={(event) =>
                            setPricePerDay(event.target.value)
                        }
                        required
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>
                {fieldErrors.price_per_day?.[0] && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {fieldErrors.price_per_day[0]}
                    </p>
                )}

                <div>
                    <label
                        htmlFor="product-deposit"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("products.edit.fields.deposit")}
                    </label>

                    <input
                        id="product-deposit"
                        type="number"
                        min="0"
                        step="1"
                        value={deposit}
                        onChange={(event) =>
                            setDeposit(event.target.value)
                        }
                        required
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>
                {fieldErrors.deposit?.[0] && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {fieldErrors.deposit[0]}
                    </p>
                )}

                <div className="md:col-span-2">
                    <label
                        htmlFor="product-battery-system"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("products.edit.fields.batterySystem")}
                    </label>

                    <select
                        id="product-battery-system"
                        value={batterySystemId}
                        onChange={(event) =>
                            setBatterySystemId(event.target.value)
                        }
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                        <option value="">
                            {t("products.edit.noBatterySystem")}
                        </option>

                        {batterySystems.map((system) => (
                            <option
                                key={system.id}
                                value={system.id}
                            >
                                {system.manufacturer} {system.name}
                                {!system.active
                                    ? ` (${t("status.inactive")})`
                                    : ""}
                            </option>
                        ))}
                    </select>
                    {fieldErrors.battery_system_id?.[0] && (
                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                            {fieldErrors.battery_system_id[0]}
                        </p>
                    )}
                </div>

                {hasBatterySystem && (
                    <>
                        <div>
                            <label
                                htmlFor="required-batteries"
                                className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                {t(
                                    "products.edit.fields.requiredBatteries"
                                )}
                            </label>

                            <input
                                id="required-batteries"
                                type="number"
                                min="0"
                                step="1"
                                value={requiredBatteries}
                                onChange={(event) =>
                                    setRequiredBatteries(
                                        event.target.value
                                    )
                                }
                                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                        {fieldErrors.required_batteries?.[0] && (
                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                {fieldErrors.required_batteries[0]}
                            </p>
                        )}

                        <div>
                            <label
                                htmlFor="required-chargers"
                                className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                {t(
                                    "products.edit.fields.requiredChargers"
                                )}
                            </label>

                            <input
                                id="required-chargers"
                                type="number"
                                min="0"
                                step="1"
                                value={requiredChargers}
                                onChange={(event) =>
                                    setRequiredChargers(
                                        event.target.value
                                    )
                                }
                                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                    </>
                )}
                {fieldErrors.required_chargers?.[0] && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {fieldErrors.required_chargers[0]}
                    </p>
                )}
            </div>

            {error && (
                <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                    {error}
                </div>
            )}

            <div className="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={saving}
                    className="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-900"
                >
                    {t("products.edit.cancel")}
                </button>

                <button
                    type="submit"
                    disabled={saving}
                    className="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200"
                >
                    {saving
                        ? t("products.edit.saving")
                        : t("products.edit.save")}
                </button>
            </div>
        </form>
    );
}