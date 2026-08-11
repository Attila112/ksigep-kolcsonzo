"use client";

import type { ReactNode } from "react";
import { useState } from "react";

import { AdminHeader } from "@/components/admin/AdminHeader";
import { AdminSidebar } from "@/components/admin/AdminSidebar";

type AdminLayoutProps = {
    children: ReactNode;
};

export function AdminLayout({
    children,
}: AdminLayoutProps) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-50 text-slate-950 dark:bg-slate-900 dark:text-slate-50">
            <div className="flex min-h-screen">
                <AdminSidebar
                    mobileOpen={sidebarOpen}
                    onClose={() => setSidebarOpen(false)}
                />

                <div className="min-w-0 flex-1">
                    <AdminHeader
                        onMenuClick={() => setSidebarOpen(true)}
                    />

                    <main>
                        {children}
                    </main>
                </div>
            </div>
        </div>
    );
}