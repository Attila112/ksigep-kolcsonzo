import type { ReactNode } from "react";
import { Footer } from "@/components/layout/Footer";
import { Header } from "@/components/layout/Header";

type PublicLayoutProps = {
    children: ReactNode;
    applicationName: string;
    productsLabel: string;
    copyright: string;
};

/**
 * A publikus weboldal közös szerkezete.
 */
export function PublicLayout({
    children,
    applicationName,
    productsLabel,
    copyright,
}: PublicLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col bg-slate-50 text-slate-950">
            <Header applicationName={applicationName}
                productsLabel={productsLabel}
            />

            <main className="flex-1">
                {children}
            </main>

            <Footer
                applicationName={applicationName}
                copyright={copyright}
            />
        </div>
    );
}