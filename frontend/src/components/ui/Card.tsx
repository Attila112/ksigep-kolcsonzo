import type { HTMLAttributes, ReactNode } from "react";

type CardProps = HTMLAttributes<HTMLElement> & {
    children: ReactNode;
};

/**
 * Általános tartalmi kártya.
 *
 * Termékekhez, admin blokkokhoz és más összetartozó
 * információk vizuális csoportosításához használható.
 */
export function Card({
    children,
    className = "",
    ...props
}: CardProps) {
    return (
        <article
            className={[
                "rounded-xl border border-slate-200 bg-white p-5",
                "text-slate-950 shadow-sm",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        >
            {children}
        </article>
    );
}