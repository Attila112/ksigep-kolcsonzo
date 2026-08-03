import type { HTMLAttributes, ReactNode } from "react";

type ContainerProps = HTMLAttributes<HTMLDivElement> & {
    children: ReactNode;
};

/**
 * Egységes szélességet és oldalsó térközt biztosít
 * az alkalmazás oldalainak.
 *
 * A className továbbadható, így az alapbeállítások
 * mellett oldalanként is bővíthető.
 */
export function Container({
    children,
    className = "",
    ...props
}: ContainerProps) {
    return (
        <div
            className={[
                "mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        >
            {children}
        </div>
    );
}