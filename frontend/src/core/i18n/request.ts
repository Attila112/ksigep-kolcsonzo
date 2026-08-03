import { hasLocale } from "next-intl";
import { getRequestConfig } from "next-intl/server";
import { routing } from "./routing";

/**
 * Betölti az adott kérés nyelvéhez tartozó fordításokat.
 *
 * A külön JSON-fájlokat itt egyetlen, névterekre bontott
 * messages objektummá egyesítjük.
 */
export default getRequestConfig(async ({ requestLocale }) => {
    const requestedLocale = await requestLocale;

    const locale = hasLocale(
        routing.locales,
        requestedLocale
    )
        ? requestedLocale
        : routing.defaultLocale;

    const [
        common,
        home,
        product,
        booking,
        inventory,
        admin,
        validation,
    ] = await Promise.all([
        import(`../../../messages/${locale}/common.json`),
        import(`../../../messages/${locale}/home.json`),
        import(`../../../messages/${locale}/product.json`),
        import(`../../../messages/${locale}/booking.json`),
        import(`../../../messages/${locale}/inventory.json`),
        import(`../../../messages/${locale}/admin.json`),
        import(`../../../messages/${locale}/validation.json`),
    ]);

    return {
        locale,
        timeZone: "Europe/Budapest",
        messages: {
            Common: common.default,
            Home: home.default,
            Product: product.default,
            Booking: booking.default,
            Inventory: inventory.default,
            Admin: admin.default,
            Validation: validation.default,
        },
    };
});