/**
 * ISO dátumot magyar formátumban jelenít meg.
 *
 * Példa:
 * 2026-08-10
 * ↓
 * 2026. 08. 10.
 */
export function formatDate(
    date: string | Date
): string {
    const value =
        typeof date === "string"
            ? new Date(date)
            : date;

    return new Intl.DateTimeFormat(
        "hu-HU",
        {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
        }
    ).format(value);
}

/**
 * ISO dátumot és időpontot magyar formátumban jelenít meg.
 *
 * Példa:
 * 2026-08-10T09:30:00.000000Z
 * ↓
 * 2026. 08. 10. 11:30
 *
 * A megjelenítés a kliens/szerver helyi időzónáját használja.
 */
export function formatDateTime(
    date: string | Date
): string {
    const value =
        typeof date === "string"
            ? new Date(date)
            : date;

    return new Intl.DateTimeFormat(
        "hu-HU",
        {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
        }
    ).format(value);
}