import { NextIntlClientProvider, hasLocale } from "next-intl";
import { getMessages, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import type { ReactNode } from "react";
import { routing } from "@/core/i18n/routing";

type LocaleLayoutProps = {
    children: ReactNode;
    params: Promise<{
        locale: string;
    }>;
};

export function generateStaticParams() {
    return routing.locales.map((locale) => ({
        locale,
    }));
}

/**
 * Elérhetővé teszi az aktuális nyelv fordításait
 * az alatta lévő oldalak és komponensek számára.
 */
export default async function LocaleLayout({
    children,
    params,
}: LocaleLayoutProps) {
    const { locale } = await params;

    if (!hasLocale(routing.locales, locale)) {
        notFound();
    }

    setRequestLocale(locale);

    const messages = await getMessages();

    return (
        <NextIntlClientProvider messages={messages}>
            {children}
        </NextIntlClientProvider>
    );
}