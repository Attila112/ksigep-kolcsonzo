import { Container } from "@/components/ui/Container";

type FooterProps = {
    applicationName: string;
    copyright: string;
};

/**
 * A publikus oldal közös lábléce.
 *
 * A végleges kapcsolati adatok, nyitvatartás és jogi linkek
 * később kerülnek ide.
 */
export function Footer({
    applicationName,
    copyright,
}: FooterProps) {
    return (
        <footer className="border-t border-slate-200 bg-white text-slate-700">
            <Container className="flex min-h-20 flex-col justify-center gap-1 py-4">
                <p className="font-semibold text-slate-950">
                    {applicationName}
                </p>

                <p className="text-sm">
                    {copyright}
                </p>
            </Container>
        </footer>
    );
}