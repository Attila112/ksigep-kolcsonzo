import type { ButtonHTMLAttributes, ReactNode } from "react";

type ButtonVariant =
    | "primary"
    | "secondary"
    | "danger";

type ButtonSize =
    | "sm"
    | "md"
    | "lg";

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
    children: ReactNode;
    variant?: ButtonVariant;
    size?: ButtonSize;
    fullWidth?: boolean;
};

/**
 * Az alkalmazás közös gomb komponense.
 *
 * A `variant` a gomb jelentését,
 * a `size` pedig a méretét határozza meg.
 */
export function Button({
    children,
    variant = "primary",
    size = "md",
    fullWidth = false,
    className = "",
    type = "button",
    ...props
}: ButtonProps) {
    const baseClasses = [
        "inline-flex items-center justify-center",
        "font-semibold",
        "transition-colors",
        "focus-visible:outline-none",
        "focus-visible:ring-2",
        "focus-visible:ring-offset-2",
        "disabled:cursor-not-allowed",
        "disabled:opacity-50",
        "rounded-md",
    ];

    const variantClasses: Record<ButtonVariant, string> = {
        primary:
            "bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-600",
        secondary:
            "border border-slate-300 bg-white text-slate-900 hover:bg-slate-100 focus-visible:ring-slate-500",
        danger:
            "bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-600",
    };

    const sizeClasses: Record<ButtonSize, string> = {
        sm: "min-h-9 px-3 py-2 text-sm",
        md: "min-h-11 px-4 py-2.5 text-base",
        lg: "min-h-12 px-6 py-3 text-lg",
    };

    return (
        <button
            type={type}
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
        </button>
    );
}