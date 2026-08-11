import "server-only";

import { cookies } from "next/headers";

const AUTH_COOKIE_NAME = "kisgep_auth_token";

/**
 * Visszaadja az aktuális Sanctum Bearer tokent.
 *
 * Csak szerveroldalon használható.
 */
export async function getAuthToken(): Promise<string | null> {
    const cookieStore = await cookies();

    return cookieStore.get(AUTH_COOKIE_NAME)?.value ?? null;
}

/**
 * Elmenti a Sanctum Bearer tokent HttpOnly cookie-ba.
 */
export async function setAuthToken(
    token: string
): Promise<void> {
    const cookieStore = await cookies();

    cookieStore.set(AUTH_COOKIE_NAME, token, {
        httpOnly: true,
        secure: process.env.NODE_ENV === "production",
        sameSite: "lax",
        path: "/",
    });
}

/**
 * Törli a bejelentkezési cookie-t.
 */
export async function clearAuthToken(): Promise<void> {
    const cookieStore = await cookies();

    cookieStore.delete(AUTH_COOKIE_NAME);
}