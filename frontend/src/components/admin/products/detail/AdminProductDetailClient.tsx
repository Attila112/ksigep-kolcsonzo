"use client";

import { useState } from "react";
import { Pencil } from "lucide-react";
import { useRouter } from "next/navigation";
import { useTranslations } from "next-intl";

import { AdminProductEditForm } from "@/components/admin/products/detail/AdminProductEditForm";
import type {
    AdminBatterySystemLookupItem,
    AdminCategoryLookupItem,
} from "@/types/adminLookup";
import type { AdminProductDetail } from "@/types/adminProduct";

type AdminProductDetailClientProps = {
    product: AdminProductDetail;
    categories: AdminCategoryLookupItem[];
    batterySystems: AdminBatterySystemLookupItem[];
};

export function AdminProductDetailClient({
    product,
    categories,
    batterySystems,
}: AdminProductDetailClientProps) {
    const t = useTranslations("Admin");
    const router = useRouter();

    const [editing, setEditing] = useState(false);

    function handleSaved() {
        setEditing(false);
        router.refresh();
    }

    if (editing) {
        return (
            <section className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950 sm:p-5">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">
                    {t("products.edit.title")}
                </h2>

                <div className="mt-6">
                    <AdminProductEditForm
                        product={product}
                        categories={categories}
                        batterySystems={batterySystems}
                        onCancel={() => setEditing(false)}
                        onSaved={handleSaved}
                    />
                </div>
            </section>
        );
    }

    return (
        <div className="flex justify-end">
            <button
                type="button"
                onClick={() => setEditing(true)}
                className="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-900"
            >
                <Pencil
                    size={17}
                    aria-hidden="true"
                />

                {t("products.edit.open")}
            </button>
        </div>
    );
}