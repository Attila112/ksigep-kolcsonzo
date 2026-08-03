import { Card } from "@/components/ui/Card";
import { Heading } from "@/components/ui/Heading";
import { Link } from "@/core/i18n/navigation";
import type { WorkType } from "@/types/workType";
import { getWorkTypeIcon } from "@/utils/getWorkTypeIcon";

type WorkTypeCardProps = {
    workType: WorkType;
};

/**
 * Egy munkatípust jelenít meg.
 *
 * A felhasználó nem konkrét gépet, hanem az elvégzendő
 * feladatot választja ki.
 */
export function WorkTypeCard({
    workType,
}: WorkTypeCardProps) {
    return (
        <Card className="h-full p-0 transition-shadow hover:shadow-md">
            <Link
                href={`/work-types/${workType.slug}`}
                className="flex h-full flex-col gap-3 p-5"
            >
                <span
                    aria-hidden="true"
                    className="text-3xl"
                >
                    {getWorkTypeIcon(workType.icon_key)}
                </span>

                <Heading level={3} size="md">
                    {workType.name}
                </Heading>

                {workType.description && (
                    <p className="text-sm text-slate-600">
                        {workType.description}
                    </p>
                )}
            </Link>
        </Card>
    );
}