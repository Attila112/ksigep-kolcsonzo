type AdminProductToolbarProps = {
    searchPlaceholder: string;
};

export function AdminProductToolbar({
    searchPlaceholder,
}: AdminProductToolbarProps) {
    return (
        <div className="mb-4 flex items-center justify-between gap-4">
            <div className="w-full max-w-md">
                <input
                    type="search"
                    placeholder={searchPlaceholder}
                    className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                />
            </div>
        </div>
    );
}