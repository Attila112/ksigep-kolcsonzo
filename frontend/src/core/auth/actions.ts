"use server";

import { redirect } from "next/navigation";
import { getTranslations } from "next-intl/server";

import { ApiError, apiRequest } from "@/core/api/api";
import {
    clearAuthToken,
    getAuthToken,
    setAuthToken,
} from "@/core/auth/authCookie";
import { login } from "@/services/authService";

export type LoginActionState = {
    error: string | null;
};

export async function loginAction(
    _previousState: LoginActionState,
    formData: FormData
): Promise<LoginActionState> {
    const locale = String(formData.get("locale") ?? "hu");

    const t = await getTranslations({
        locale,
        namespace: "Auth",
    });
    const email = String(formData.get("email") ?? "");
    const password = String(formData.get("password") ?? "");

    if (!email || !password) {
        return {
            error: t("login.requiredFields"),
        };
    }

    try {
        const response = await login({
            email,
            password,
        });

        await setAuthToken(response.token);
    } catch (error) {
        if (error instanceof ApiError) {
            return {
                error: error.message,
            };
        }

        return {
            error: t("login.unknownError"),
        };
    }

    redirect(`/${locale}/admin`);
}

export async function logoutAction(
    formData: FormData
): Promise<void> {
    const locale = String(formData.get("locale") ?? "hu");
    const token = await getAuthToken();

    if (token) {
        try {
            await apiRequest("/logout", {
                method: "POST",
                token,
            });
        } catch {
            /*
             * A helyi auth cookie-t akkor is töröljük,
             * ha a backend token már nem érvényes.
             */
        }
    }

    await clearAuthToken();

    redirect(`/${locale}/login`);
}