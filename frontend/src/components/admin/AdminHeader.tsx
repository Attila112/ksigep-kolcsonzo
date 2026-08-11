"use client";

import { Bell, LogOut, Menu, Search } from "lucide-react";
import { useLocale, useTranslations } from "next-intl";

import { logoutAction } from "@/core/auth/actions";

type AdminHeaderProps = {
    onMenuClick: () => void;
};

export function AdminHeader({
    onMenuClick,
}: AdminHeaderProps) {
    const locale = useLocale();
    const t = useTranslations("Admin");

    return (
        <header className="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 dark:border-slate-800 dark:bg-slate-950 lg:px-6">
            <div className="flex items-center gap-3">
                <button
                    type="button"
                    onClick={onMenuClick}
                    className="inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900 lg:hidden"
                    aria-label={t("header.openMenu")}
                >
                    <Menu size={20} />
                </button>

                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {t("header.title")}
                </p>
            </div>

            <div className="flex items-center gap-1">
                <button
                    type="button"
                    className="inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900"
                    aria-label={t("header.search")}
                >
                    <Search size={19} />
                </button>

                <button
                    type="button"
                    className="inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900"
                    aria-label={t("header.notifications")}
                >
                    <Bell size={19} />
                </button>

                <form action={logoutAction}>
                    <input
                        type="hidden"
                        name="locale"
                        value={locale}
                    />

                    <button
                        type="submit"
                        className="ml-2 inline-flex h-10 items-center gap-2 rounded-md px-3 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        <LogOut size={17} aria-hidden="true" />
                        {t("header.logout")}
                    </button>
                </form>
            </div>
        </header>
    );
}