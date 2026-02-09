import { Badge } from '@/shared/components/ui/badge';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/shared/components/ui/sheet';
import { type Activity as ActivityModel } from '@/types/models';
import { format } from 'date-fns';
import { useTranslation } from 'react-i18next';

type Activity = Omit<ActivityModel, 'properties'> & {
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    translated_description?: string;
    properties: Record<string, unknown> | unknown[];
};

type ActivityDetailsSheetProps = {
    activity: Activity | null;
    onClose: () => void;
};

const PropertiesTable = ({ properties, level = 0 }: { properties: Record<string, unknown> | unknown[]; level?: number }) => {
    if (!properties) {
        return null;
    }

    const entries = Array.isArray(properties)
        ? properties.map((value, index) => [String(index), value] as [string, unknown])
        : Object.entries(properties);

    if (entries.length === 0) {
        return null;
    }

    const isObject = (value: unknown) => value !== null && typeof value === 'object' && !Array.isArray(value);

    return (
        <div className="w-full min-w-0">
            <table className="w-full table-fixed">
                <thead>
                    <tr className="border-b">
                        <th className="w-[30%] p-2 text-left text-sm font-medium whitespace-nowrap">Property</th>
                        <th className="p-2 text-left text-sm font-medium">Value</th>
                    </tr>
                </thead>
                <tbody>
                    {entries.map(([key, value]) => (
                        <tr key={key} className="border-b last:border-0">
                            <td className="p-2 align-top text-sm font-medium whitespace-nowrap">{key}</td>
                            <td className="p-2 align-top text-sm wrap-break-word">
                                {isObject(value) && level < 1 ? (
                                    <div className="w-full min-w-0 overflow-hidden rounded-md border">
                                        <PropertiesTable properties={value as Record<string, unknown>} level={level + 1} />
                                    </div>
                                ) : Array.isArray(value) ? (
                                    <pre className="overflow-hidden rounded bg-muted p-2 text-xs wrap-break-word whitespace-pre-wrap">
                                        {JSON.stringify(value, null, 2)}
                                    </pre>
                                ) : value === null ? (
                                    <span className="text-muted-foreground italic">null</span>
                                ) : typeof value === 'boolean' ? (
                                    <Badge variant={value ? 'default' : 'secondary'}>{String(value)}</Badge>
                                ) : (
                                    <span className="wrap-break-word">{String(value)}</span>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export function ActivityDetailsSheet({ activity, onClose }: ActivityDetailsSheetProps) {
    const { t } = useTranslation();

    const descriptionText =
        activity?.translation !== undefined
            ? t(activity.translation.key, activity.translation.replacements as never)
            : (activity?.translated_description ?? activity?.description);
    const descriptionTextString = descriptionText ? String(descriptionText) : '';

    return (
        <Sheet open={!!activity} onOpenChange={onClose}>
            <SheetContent className="inset-y-0 right-0 m-0 w-full gap-0 overflow-hidden rounded-tl-xl rounded-bl-xl border-0 p-0 sm:max-w-2xl">
                <div className="flex h-full flex-col">
                    <SheetHeader className="p-0 px-6 pt-6 pb-4">
                        <div className="px-6 pt-6 pb-4">
                            <SheetTitle>{t('admin.activities.modal.heading')}</SheetTitle>
                            <SheetDescription>{t('admin.activities.modal.description')}</SheetDescription>
                        </div>
                    </SheetHeader>
                    {activity && (
                        <div className="flex-1 overflow-x-hidden overflow-y-auto px-6 pb-6">
                            <div className="space-y-6">
                                <div className="overflow-hidden rounded-lg border">
                                    <table className="w-full table-fixed">
                                        <tbody>
                                            <tr className="border-b">
                                                <td className="w-[30%] p-3 align-top font-medium whitespace-nowrap">
                                                    {t('admin.activities.modal.description')}
                                                </td>
                                            </tr>
                                            {descriptionTextString && (
                                                <tr className="border-b">
                                                    <td className="w-[30%] p-3 align-top font-medium whitespace-nowrap">Description</td>
                                                    <td className="p-3 text-sm wrap-break-word">{descriptionTextString}</td>
                                                </tr>
                                            )}
                                            <tr className="border-b">
                                                <td className="w-[30%] p-3 align-top font-medium whitespace-nowrap">
                                                    {t('admin.activities.modal.event')}
                                                </td>
                                                <td className="p-3">
                                                    <Badge variant="secondary">{activity.event}</Badge>
                                                </td>
                                            </tr>
                                            <tr className="border-b">
                                                <td className="w-[30%] p-3 align-top font-medium whitespace-nowrap">
                                                    {t('admin.activities.modal.log_name')}
                                                </td>
                                                <td className="p-3">
                                                    <Badge variant="outline">{activity.log_name}</Badge>
                                                </td>
                                            </tr>
                                            <tr className="border-b">
                                                <td className="w-[30%] p-3 align-top font-medium whitespace-nowrap">
                                                    {t('admin.activities.modal.causer')}
                                                </td>
                                                <td className="p-3 wrap-break-word">{activity.causer ? activity.causer.name : 'System'}</td>
                                            </tr>
                                            <tr>
                                                <td className="w-[30%] p-3 align-top font-medium whitespace-nowrap">
                                                    {t('admin.activities.modal.timestamp')}
                                                </td>
                                                <td className="p-3 wrap-break-word">
                                                    {format(new Date(activity.created_at), 'MMMM d, yyyy HH:mm:ss')}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {Object.keys(activity.properties).length > 0 && (
                                    <div>
                                        <p className="mb-3 text-sm font-medium">{t('admin.activities.modal.properties')}</p>
                                        <div className="overflow-hidden rounded-lg border">
                                            <div className="overflow-x-auto">
                                                <PropertiesTable properties={activity.properties} />
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </SheetContent>
        </Sheet>
    );
}
