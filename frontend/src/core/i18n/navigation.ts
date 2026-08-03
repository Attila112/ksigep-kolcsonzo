import { createNavigation } from "next-intl/navigation";
import { routing } from "./routing";

/**
 * Nyelvtudatos navigációs eszközök.
 *
 * Ezeket használjuk majd a Next.js alap Link, redirect,
 * useRouter és usePathname megoldásai helyett.
 */
export const {
    Link,
    redirect,
    usePathname,
    useRouter,
    getPathname,
} = createNavigation(routing);