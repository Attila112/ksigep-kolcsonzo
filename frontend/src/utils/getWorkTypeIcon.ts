const workTypeIcons: Record<string, string> = {
    garden: "🌿",
    cleaning: "🧹",
    woodworking: "🪵",
    diy: "🛠️",
    concrete: "🧱",
    painting: "🎨",
};

/**
 * Az adatbázisban tárolt icon_key értékhez
 * megjeleníthető ideiglenes ikont rendel.
 *
 * A végleges arculatnál az emojik lecserélhetők
 * SVG vagy ikonkönyvtári ikonokra.
 */
export function getWorkTypeIcon(
    iconKey: string | null
): string {
    if (!iconKey) {
        return "🧰";
    }

    return workTypeIcons[iconKey] ?? "🧰";
}