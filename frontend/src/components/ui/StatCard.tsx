import type { ReactNode } from "react";

type StatCardProps = {
    title: string;
    value: string | number;
    icon?: ReactNode;
    description?: string;
};

export function StatCard({
    title,
    value,
    icon,
    description,
}: StatCardProps) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">
                        {title}
                    </p>

                    <p className="mt-2 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">
                        {value}
                    </p>
                </div>

                {icon && (
                    <div className="text-slate-400 dark:text-slate-500">
                        {icon}
                    </div>
                )}
            </div>

            {description && (
                <p className="mt-3 text-sm text-slate-500 dark:text-slate-400">
                    {description}
                </p>
            )}
        </div>
    );
}