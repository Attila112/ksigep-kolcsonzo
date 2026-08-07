import { notFound } from "next/navigation";
//import { setRequestLocale } from "next-intl/server";

import { ProductList } from "@/components/product/ProductList";
import { Container } from "@/components/ui/Container";
import { Heading } from "@/components/ui/Heading";
import { Section } from "@/components/ui/Section";
import { ApiError } from "@/core/api/api";
import { getWorkTypeProducts } from "@/services/workTypeService";
import { getWorkTypeIcon } from "@/utils/getWorkTypeIcon";
import { getTranslations, setRequestLocale } from "next-intl/server";

type WorkTypePageProps = {
    params: Promise<{
        locale: string;
        slug: string;
    }>;
};

export default async function WorkTypePage({
    params,
}: WorkTypePageProps) {
    const { locale, slug } = await params;

    setRequestLocale(locale);
    const t = await getTranslations("Home");

    let data;

    try {
        data = await getWorkTypeProducts(slug);
    } catch (error) {
        if (error instanceof ApiError && error.status === 404) {
            notFound();
        }

        throw error;
    }

    return (
        <>
            <Section>
                <Container>
                    <div className="flex items-start gap-4">
                        <span
                            aria-hidden="true"
                            className="text-4xl"
                        >
                            {getWorkTypeIcon(data.work_type.icon_key)}
                        </span>

                        <div>
                            <Heading level={1} size="xl">
                                {data.work_type.name}
                            </Heading>

                            {data.work_type.description && (
                                <p className="mt-4 max-w-2xl text-lg text-slate-600">
                                    {data.work_type.description}
                                </p>
                            )}
                        </div>
                    </div>
                </Container>
            </Section>

            <Section className="pt-0">
                <Container>
                    <Heading level={2} size="lg">
                        {t("workTypeDetails.recommendedProducts")}
                    </Heading>

                    <div className="mt-6">
                        <ProductList products={data.products} />
                    </div>
                </Container>
            </Section>
        </>
    );
}