"use client";

import {
    Battery,
    Boxes,
    CalendarDays,
    LayoutDashboard,
    Package,
    Settings,
} from "lucide-react";
import { useTranslations } from "next-intl";
import { usePathname } from "next/navigation";

import { Link } from "@/core/i18n/navigation";

type AdminSidebarProps = {
    mobileOpen: boolean;
    onClose: () => void;
};

type SidebarItem = {
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
};

export function AdminSidebar({
    mobileOpen,
    onClose,
}: AdminSidebarProps) {
    const t = useTranslations("Admin");
    const pathname = usePathname();

    const items: SidebarItem[] = [
        {
            label: t("navigation.dashboard"),
            href: "/admin",
            icon: LayoutDashboard,
        },
        {
            label: t("navigation.products"),
            href: "/admin/products",
            icon: Package,
        },
        {
            label: t("navigation.inventory"),
            href: "/admin/inventory",
            icon: Boxes,
        },
        {
            label: t("navigation.batteries"),
            href: "/admin/batteries",
            icon: Battery,
        },
        {
            label: t("navigation.bookings"),
            href: "/admin/bookings",
            icon: CalendarDays,
        },
        {
            label: t("navigation.settings"),
            href: "/admin/settings",
            icon: Settings,
        },
    ];

    const renderSidebarContent = (
        closeAfterNavigation: boolean
    ) => (
        <div className="flex min-h-screen flex-col">
            <div className="border-b border-slate-200 px-5 py-5 dark:border-slate-800">
                <p className="font-bold text-slate-950 dark:text-slate-50">
                    Kisgép-kölcsönző
                </p>

                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Admin
                </p>
            </div>

            <nav className="flex flex-col gap-1 p-3">
                {items.map((item) => {
                    const Icon = item.icon;

                    const isActive =
                        item.href === "/admin"
                            ? /^\/[^/]+\/admin\/?$/.test(pathname)
                            : pathname.includes(item.href);

                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            onClick={
                                closeAfterNavigation
                                    ? onClose
                                    : undefined
                            }
                            className={[
                                "flex min-h-11 items-center gap-3 rounded-md px-3 text-sm font-medium transition-colors",
                                isActive
                                    ? "bg-slate-100 text-slate-950 dark:bg-slate-800 dark:text-white"
                                    : "text-slate-700 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white",
                            ].join(" ")}
                        >
                            <Icon
                                size={18}
                                aria-hidden="true"
                            />

                            {item.label}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );

    return (
        <>
            {/* Desktop sidebar */}
            <aside className="hidden w-64 shrink-0 border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:block">
                {renderSidebarContent(false)}
            </aside>

            {/* Mobile / tablet sidebar */}
            {mobileOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <button
                        type="button"
                        aria-label="Menü bezárása"
                        onClick={onClose}
                        className="absolute inset-0 bg-black/40"
                    />

                    <aside className="relative z-10 h-full w-72 border-r border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-950">
                        {renderSidebarContent(true)}
                    </aside>
                </div>
            )}
        </>
    );
}