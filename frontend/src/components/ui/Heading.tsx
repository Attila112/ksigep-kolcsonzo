import type {
    ComponentPropsWithoutRef,
    ReactNode,
} from "react";

type HeadingLevel = 1 | 2 | 3 | 4;
type HeadingSize = "sm" | "md" | "lg" | "xl";
type HeadingTag = "h1" | "h2" | "h3" | "h4";

type HeadingProps = ComponentPropsWithoutRef<"h2"> & {
    children: ReactNode;
    level?: HeadingLevel;
    size?: HeadingSize;
};

export function Heading({
    children,
    level = 2,
    size = "lg",
    className = "",
    ...props
}: HeadingProps) {
    const tagByLevel: Record<HeadingLevel, HeadingTag> = {
        1: "h1",
        2: "h2",
        3: "h3",
        4: "h4",
    };

    const sizeClasses: Record<HeadingSize, string> = {
        sm: "text-lg font-semibold",
        md: "text-xl font-semibold",
        lg: "text-2xl font-bold",
        xl: "text-3xl font-bold sm:text-4xl",
    };

    const Component = tagByLevel[level];

    return (
        <Component
            className={[
                "tracking-tight",
                sizeClasses[size],
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        >
            {children}
        </Component>
    );
}