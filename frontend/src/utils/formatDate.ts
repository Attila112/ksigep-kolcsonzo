/**
 * ISO dátumot magyar formátumban jelenít meg.
 *
 * Példa:
 * 2026-08-10
 * ↓
 * 2026. 08. 10.
 */
export function formatDate(date: string | Date): string {
    const value = typeof date === "string"
        ? new Date(date)
        : date;

    return new Intl.DateTimeFormat("hu-HU", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }).format(value);
}