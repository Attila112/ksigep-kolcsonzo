import { getTranslations } from "next-intl/server";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Section } from "@/components/ui/Section";
import { ButtonLink } from "@/components/ui/ButtonLink";

/**
 * A főoldal bevezető szekciója.
 *
 * A feliratok a Home fordítási névtérből érkeznek,
 * így később új nyelv hozzáadásakor nem kell
 * módosítani magát a komponenst.
 */
export async function Hero() {
    const t = await getTranslations("Home");

    return (
        <Section>
            <Container>
                <Heading
                    level={1}
                    size="xl"
                >
                    {t("hero.title")}
                </Heading>

                <p className="mt-6 max-w-2xl text-lg text-slate-600">
                    {t("hero.description")}
                </p>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                    <ButtonLink
                        href="/products"
                        size="lg"
                    >
                        {t("hero.browseProducts")}
                    </ButtonLink>

                    <ButtonLink
                        href="/#work-types"
                        variant="secondary"
                        size="lg"
                    >
                        {t("hero.findByTask")}
                    </ButtonLink>
                </div>
            </Container>
        </Section>
    );
}