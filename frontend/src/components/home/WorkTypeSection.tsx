import { getTranslations } from "next-intl/server";
import { WorkTypeCard } from "@/components/home/WorkTypeCard";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Section } from "@/components/ui/Section";
import type { WorkType } from "@/types/workType";

type WorkTypeSectionProps = {
    workTypes: WorkType[];
};

/**
 * A főoldal feladatalapú gépválasztó szekciója.
 */
export async function WorkTypeSection({
    workTypes,
}: WorkTypeSectionProps) {
    const t = await getTranslations("Home");

    if (workTypes.length === 0) {
        return null;
    }

    return (
        <Section
            id="work-types"
            className="bg-white"
        >
            <Container>
                <Heading level={2} size="lg">
                    {t("workTypes.title")}
                </Heading>

                <p className="mt-3 max-w-2xl text-slate-600">
                    {t("workTypes.description")}
                </p>

                <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {workTypes.map((workType) => (
                        <WorkTypeCard
                            key={workType.id}
                            workType={workType}
                        />
                    ))}
                </div>
            </Container>
        </Section>
    );
}