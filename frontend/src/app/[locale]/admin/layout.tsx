import type { ReactNode } from "react";
import { redirect } from "next/navigation";

import { AdminLayout } from "@/components/admin/AdminLayout";
import { getAuthToken } from "@/core/auth/authCookie";

type AdminRouteLayoutProps = {
    children: ReactNode;
    params: Promise<{
        locale: string;
    }>;
};

export default async function AdminRouteLayout({
    children,
    params,
}: AdminRouteLayoutProps) {
    const { locale } = await params;

    const token = await getAuthToken();

    if (!token) {
        redirect(`/${locale}/login`);
    }

    return (
        <AdminLayout>
            {children}
        </AdminLayout>
    );
}