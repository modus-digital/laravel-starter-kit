import { Button } from '@/shared/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import AppLayout from '@/shared/layouts/app-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type Client = {
    id: string;
    name: string;
};

type Activity = {
    id: string;
    description: string;
    translated_description?: string;
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    event?: string;
    causer?: {
        id: string;
        name: string;
        email: string;
    };
    properties?: Record<string, unknown>;
    created_at: string;
};

type PageProps = SharedData & {
    client: Client;
    activities: {
        data: Activity[];
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
};

export default function Activities() {
    const { client, activities } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const renderActivityDescription = (activity: Activity) => {
        if (activity.translation) {
            return t(activity.translation.key, activity.translation.replacements as never);
        }

        if (activity.translated_description) {
            return activity.translated_description;
        }

        return t(activity.description as never);
    };

    return (
        <AppLayout>
            <Head title={t('admin.clients.activities', 'Activities')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href={`/clients/${client.id}`}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.clients.activities', 'Activities')}</h1>
                        <p className="text-sm text-muted-foreground">{t('clients.view_activities', 'View activity log')}</p>
                    </div>
                </div>

                <div className="overflow-hidden rounded-lg border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('common.labels.description')}</TableHead>
                                <TableHead>{t('admin.activities.table.causer', 'Performed by')}</TableHead>
                                <TableHead>{t('common.labels.created_at')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {activities.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={3} className="h-24 text-center text-sm text-muted-foreground">
                                        {t('admin.clients.no_activities', 'No activities found.')}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                activities.data.map((activity) => (
                                    <TableRow key={activity.id}>
                                        <TableCell>{String(renderActivityDescription(activity))}</TableCell>
                                        <TableCell>
                                            {activity.causer ? (
                                                <div>
                                                    <p className="font-medium">{activity.causer.name}</p>
                                                    <p className="text-sm text-muted-foreground">{activity.causer.email}</p>
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell>{format(new Date(activity.created_at), 'PPp')}</TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    {activities.last_page > 1 && (
                        <div className="flex items-center justify-end gap-2 border-t p-4">
                            {activities.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url || '#'}
                                    className={`rounded px-3 py-1 text-sm ${
                                        link.active ? 'bg-primary text-primary-foreground' : 'bg-background text-foreground hover:bg-muted'
                                    } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                >
                                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
