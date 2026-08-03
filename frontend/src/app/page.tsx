import { redirect } from "next/navigation";
import { routing } from "@/core/i18n/routing";

export default function RootPage() {
    redirect(`/${routing.defaultLocale}`);
}