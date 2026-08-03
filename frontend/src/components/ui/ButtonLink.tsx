import type { ComponentProps, ReactNode } from "react";
import { Link } from "@/core/i18n/navigation";

type ButtonLinkVariant = "primary" | "secondary";
type ButtonLinkSize = "sm" | "md" | "lg";

type ButtonLinkProps = Omit<ComponentProps<typeof Link>, "children"> & {
    children: ReactNode;
    variant?: ButtonLinkVariant;
    size?: ButtonLinkSize;
    fullWidth?: boolean;
};

/**
 * Gomb megjelenésű navigációs link.
 *
 * Navigációhoz használjuk. Űrlap elküldéséhez
 * továbbra is a Button komponens való.
 */
export function ButtonLink({
    children,
    variant = "primary",
    size = "md",
    fullWidth = false,
    className = "",
    ...props
}: ButtonLinkProps) {
    const baseClasses = [
        "inline-flex items-center justify-center",
        "font-semibold",
        "transition-colors",
        "focus-visible:outline-none",
        "focus-visible:ring-2",
        "focus-visible:ring-offset-2",
        "rounded-md",
    ];

    const variantClasses: Record<ButtonLinkVariant, string> = {
        primary:
            "bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-600",
        secondary:
            "border border-slate-300 bg-white text-slate-900 hover:bg-slate-100 focus-visible:ring-slate-500",
    };

    const sizeClasses: Record<ButtonLinkSize, string> = {
        sm: "min-h-9 px-3 py-2 text-sm",
        md: "min-h-11 px-4 py-2.5 text-base",
        lg: "min-h-12 px-6 py-3 text-lg",
    };

    return (
        <Link
            className={[
                ...baseClasses,
                variantClasses[variant],
                sizeClasses[size],
                fullWidth ? "w-full" : "",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        >
            {children}
        </Link>
    );
}