/**
 * Forint összeget magyar formátumban jelenít meg.
 *
 * Példa:
 * 8000 → 8 000 Ft
 */
export function formatCurrency(value: number): string {
    return new Intl.NumberFormat("hu-HU", {
        style: "currency",
        currency: "HUF",
        maximumFractionDigits: 0,
    }).format(value);
}