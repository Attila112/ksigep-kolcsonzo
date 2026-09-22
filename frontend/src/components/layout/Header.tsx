"use client";

import { useBookingCart } from "@/components/booking/BookingCartProvider";
import { Container } from "@/components/ui/Container";
import { Link } from "@/core/i18n/navigation";

type HeaderProps = {
    applicationName: string;
    productsLabel: string;
    bookingLabel: string;
};

/**
 * A publikus oldal közös fejléce.
 *
 * Egyelőre csak a márka ideiglenes nevét és az alap navigációt
 * tartalmazza. A végleges logó és arculat később kerül bele.
 */
export function Header({
    applicationName,
    productsLabel,
    bookingLabel,
}: HeaderProps) {
    const { items } = useBookingCart();

    const cartItemCount = items.length;

    return (
        <header className="border-b border-slate-200 bg-white text-slate-950">
            <Container className="flex min-h-16 items-center justify-between gap-6">
                <Link
                    href="/"
                    className="font-bold tracking-tight"
                >
                    {applicationName}
                </Link>

                <nav
                    aria-label={bookingLabel}
                    className="flex items-center gap-5"
                >
                    <Link
                        href="/products"
                        className="font-medium text-slate-700 hover:text-slate-950"
                    >
                        {productsLabel}
                    </Link>

                    <Link
                        href="/booking"
                        className="font-medium text-slate-700 hover:text-slate-950"
                    >
                        {bookingLabel}

                        {cartItemCount > 0 && (
                            <>
                                {" ("}
                                {cartItemCount}
                                {")"}
                            </>
                        )}
                    </Link>
                </nav>
            </Container>
        </header>
    );
}