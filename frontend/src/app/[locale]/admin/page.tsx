import { getTranslations, setRequestLocale } from "next-intl/server";

import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Section } from "@/components/ui/Section";

type AdminPageProps = {
    params: Promise<{
        locale: string;
    }>;
};

export default async function AdminPage({
    params,
}: AdminPageProps) {
    const { locale } = await params;

    setRequestLocale(locale);

    const t = await getTranslations("Admin");

    return (
        <Section>
            <Container>
                <Heading level={1} size="xl">
                    {t("dashboard.title")}
                </Heading>

                <p className="mt-4 text-slate-600 dark:text-slate-400">
                    {t("dashboard.description")}
                </p>
            </Container>
        </Section>
    );
}