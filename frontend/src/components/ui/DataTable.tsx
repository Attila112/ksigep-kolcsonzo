import type { ReactNode } from "react";

type DataTableProps = {
    children: ReactNode;
    className?: string;
};

type DataTableHeaderProps = {
    children: ReactNode;
};

type DataTableBodyProps = {
    children: ReactNode;
};

type DataTableRowProps = {
    children: ReactNode;
};

type DataTableHeadProps = {
    children: ReactNode;
    className?: string;
};

type DataTableCellProps = {
    children: ReactNode;
    className?: string;
};

export function DataTable({
    children,
    className = "",
}: DataTableProps) {
    return (
        <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
            <table
                className={[
                    "w-full min-w-max border-collapse text-left text-sm",
                    className,
                ].join(" ")}
            >
                {children}
            </table>
        </div>
    );
}

export function DataTableHeader({
    children,
}: DataTableHeaderProps) {
    return (
        <thead className="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900">
            {children}
        </thead>
    );
}

export function DataTableBody({
    children,
}: DataTableBodyProps) {
    return (
        <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
            {children}
        </tbody>
    );
}

export function DataTableRow({
    children,
}: DataTableRowProps) {
    return (
        <tr className="transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/60">
            {children}
        </tr>
    );
}

export function DataTableHead({
    children,
    className = "",
}: DataTableHeadProps) {
    return (
        <th
            scope="col"
            className={[
                "whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400",
                className,
            ].join(" ")}
        >
            {children}
        </th>
    );
}

export function DataTableCell({
    children,
    className = "",
}: DataTableCellProps) {
    return (
        <td
            className={[
                "px-4 py-3 text-slate-700 dark:text-slate-300",
                className,
            ].join(" ")}
        >
            {children}
        </td>
    );
}