import { Container } from "@/components/ui/Container";
import { Link } from "@/core/i18n/navigation";

type HeaderProps = {
    applicationName: string;
    productsLabel: string;
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
}: HeaderProps) {
    return (
        <header className="border-b border-slate-200 bg-white text-slate-950">
            <Container className="flex min-h-16 items-center justify-between gap-6">
                <Link
                    href="/"
                    className="font-bold tracking-tight"
                >
                    {applicationName}
                </Link>

                <nav aria-label="Fő navigáció">
                    <Link
                        href="/products"
                        className="font-medium text-slate-700 hover:text-slate-950"
                    >
                        {productsLabel}
                    </Link>
                </nav>
            </Container>
        </header>
    );
}