import { setRequestLocale } from "next-intl/server";

import { LoginForm } from "@/components/auth/LoginForm";

type LoginPageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function LoginPage({
    params,
}: LoginPageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4 dark:bg-slate-950">
            <div className="w-full max-w-md">
                <LoginForm locale={locale} />
            </div>
        </div>
    );
}