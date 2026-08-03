import { defineRouting } from "next-intl/routing";

/**
 * Az alkalmazás által támogatott nyelvek központi beállítása.
 *
 * Később elég lesz az "en" nyelvet hozzáadni a locales tömbhöz,
 * és elkészíteni az angol fordítási fájlokat.
 */
export const routing = defineRouting({
    locales: ["hu"],
    defaultLocale: "hu",
    localePrefix: "always",
});