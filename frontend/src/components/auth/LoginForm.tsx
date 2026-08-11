"use client";

import { useActionState } from "react";
import { useTranslations } from "next-intl";

import {
    loginAction,
    type LoginActionState,
} from "@/core/auth/actions";

type LoginFormProps = {
    locale: string;
};

const initialState: LoginActionState = {
    error: null,
};

export function LoginForm({
    locale,
}: LoginFormProps) {
    const [state, formAction, pending] = useActionState(
        loginAction,
        initialState
    );
    const t = useTranslations("Auth");

    return (
        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h1 className="text-2xl font-bold text-slate-950 dark:text-white">
                    {t("login.title")}
                </h1>

                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {t("login.description")}
                </p>
            </div>

            <form
                action={formAction}
                className="mt-6 space-y-5"
            >
                <input
                    type="hidden"
                    name="locale"
                    value={locale}
                />

                <div>
                    <label
                        htmlFor="email"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        {t("login.email")}
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        autoComplete="email"
                        required
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        htmlFor="password"
                        className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                         {t("login.password")}
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autoComplete="current-password"
                        required
                        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                </div>

                {state.error && (
                    <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                        {state.error}
                    </div>
                )}

                <button
                    type="submit"
                    disabled={pending}
                    className="flex w-full items-center justify-center rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200"
                >
                    {pending
                        ? t("login.submitting")
                        : t("login.submit")}
                </button>
            </form>
        </div>
    );
}