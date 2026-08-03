import type {
    HTMLAttributes,
    ReactNode,
} from "react";

type SectionProps =
    HTMLAttributes<HTMLElement> & {
        children: ReactNode;
    };

/**
 * Az oldal nagyobb tartalmi blokkjainak közös
 * burkoló komponense.
 */
export function Section({
    children,
    className = "",
    ...props
}: SectionProps) {
    return (
        <section
            className={[
                "py-10 sm:py-14 lg:py-20",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        >
            {children}
        </section>
    );
}