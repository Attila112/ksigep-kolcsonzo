"use client";

import * as React from "react";
import {
    DayPicker,
    getDefaultClassNames,
    type Locale,
} from "react-day-picker";
import {
    ChevronDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "lucide-react";

import { cn } from "@/lib/utils";

function Calendar({
    className,
    classNames,
    showOutsideDays = true,
    captionLayout = "label",
    locale,
    formatters,
    components,
    ...props
}: React.ComponentProps<typeof DayPicker> & {
    locale?: Partial<Locale>;
}) {
    const defaultClassNames =
        getDefaultClassNames();

    return (
        <DayPicker
            showOutsideDays={showOutsideDays}
            captionLayout={captionLayout}
            locale={locale}
            className={cn(
                "w-fit text-slate-900",
                className
            )}
            formatters={{
                formatMonthDropdown: (date) =>
                    date.toLocaleString(
                        locale?.code,
                        {
                            month: "short",
                        }
                    ),
                ...formatters,
            }}
            classNames={{
                root: cn(
                    "w-fit",
                    defaultClassNames.root
                ),

                months: cn(
                    "relative flex flex-col gap-10 xl:flex-row xl:gap-14",
                    defaultClassNames.months
                ),

                month: cn(
                    "flex w-full flex-col gap-4",
                    defaultClassNames.month
                ),

                nav: cn(
                    "absolute inset-x-0 top-0 z-10 flex items-center justify-between",
                    defaultClassNames.nav
                ),

                button_previous: cn(
                    "flex size-9 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 disabled:pointer-events-none disabled:opacity-30",
                    defaultClassNames.button_previous
                ),

                button_next: cn(
                    "flex size-9 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 disabled:pointer-events-none disabled:opacity-30",
                    defaultClassNames.button_next
                ),

                month_caption: cn(
                    "flex h-9 items-center justify-center px-12",
                    defaultClassNames.month_caption
                ),

                caption_label: cn(
                    "text-sm font-semibold capitalize text-slate-900",
                    defaultClassNames.caption_label
                ),

                month_grid: cn(
                    "w-full border-collapse",
                    defaultClassNames.month_grid
                ),

                weekdays: cn(
                    "flex",
                    defaultClassNames.weekdays
                ),

                weekday: cn(
                    "w-10 flex-1 py-1 text-center text-xs font-medium text-slate-400 sm:w-11",
                    defaultClassNames.weekday
                ),

                week: cn(
                    "mt-1 flex w-full",
                    defaultClassNames.week
                ),

                day: cn(
                    "group/day relative size-10 p-0 text-center sm:size-11",
                    defaultClassNames.day
                ),

                day_button: cn(
                    "relative z-10 flex size-10 items-center justify-center rounded-lg text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 sm:size-11",
                    "group-[.availability-available]/day:after:absolute group-[.availability-available]/day:after:bottom-1 group-[.availability-available]/day:after:size-1 group-[.availability-available]/day:after:rounded-full group-[.availability-available]/day:after:bg-emerald-500",
                    "group-[.availability-low]/day:after:absolute group-[.availability-low]/day:after:bottom-1 group-[.availability-low]/day:after:size-1 group-[.availability-low]/day:after:rounded-full group-[.availability-low]/day:after:bg-amber-500",
                    defaultClassNames.day_button
                ),

                range_start: cn(
                    "rounded-l-lg bg-blue-50",
                    defaultClassNames.range_start
                ),

                range_middle: cn(
                    "rounded-none bg-blue-50",
                    defaultClassNames.range_middle
                ),

                range_end: cn(
                    "rounded-r-lg bg-blue-50",
                    defaultClassNames.range_end
                ),

                selected: cn(
                    "[&>button]:bg-blue-600 [&>button]:text-white [&>button]:hover:bg-blue-600",
                    defaultClassNames.selected
                ),

                today: cn(
                    "[&>button]:font-bold [&>button]:ring-1 [&>button]:ring-slate-300",
                    defaultClassNames.today
                ),

                outside: cn(
                    "[&>button]:text-slate-300",
                    defaultClassNames.outside
                ),

                disabled: cn(
                    "[&>button]:cursor-not-allowed [&>button]:text-slate-300 [&>button]:opacity-60 [&>button]:hover:bg-transparent",
                    defaultClassNames.disabled
                ),

                hidden: cn(
                    "invisible",
                    defaultClassNames.hidden
                ),

                ...classNames,
            }}
            components={{
                Chevron: ({
                    className:
                        chevronClassName,
                    orientation,
                    ...chevronProps
                }) => {
                    if (
                        orientation === "left"
                    ) {
                        return (
                            <ChevronLeftIcon
                                className={cn(
                                    "size-4",
                                    chevronClassName
                                )}
                                {...chevronProps}
                            />
                        );
                    }

                    if (
                        orientation === "right"
                    ) {
                        return (
                            <ChevronRightIcon
                                className={cn(
                                    "size-4",
                                    chevronClassName
                                )}
                                {...chevronProps}
                            />
                        );
                    }

                    return (
                        <ChevronDownIcon
                            className={cn(
                                "size-4",
                                chevronClassName
                            )}
                            {...chevronProps}
                        />
                    );
                },

                ...components,
            }}
            {...props}
        />
    );
}

export { Calendar };