import { PaginatedDataTable } from '@/shared/components/paginated-data-table';
import { ActivityDetailsSheet } from '@/shared/components/ui/activity-details-sheet';
import { Badge } from '@/shared/components/ui/badge';
import { Input } from '@/shared/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type SharedData } from '@/types';
import { type Activity as ActivityModel } from '@/types/models';
import { Head } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

type Activity = ActivityModel & {
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    translated_description?: string;
    properties: Record<string, unknown>;
    event?: string;
};

type PageProps = SharedData & {
    activities: Activity[];
    filters: {
        log_name?: string;
        event?: string;
        causer_id?: string;
        date_from?: string;
        date_to?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    logNames: string[];
};

export default function Index({ activities, filters, logNames }: PageProps) {
    const { t } = useTranslation();

    const [selectedActivity, setSelectedActivity] = useState<Activity | null>(null);

    // Client-side filter states (not used for backend filtering anymore, kept for UI)
    const [logNameFilter, setLogNameFilter] = useState(filters.log_name || '');
    const [eventFilter, setEventFilter] = useState(filters.event || '');
    const [dateFromFilter, setDateFromFilter] = useState(filters.date_from || '');
    const [dateToFilter, setDateToFilter] = useState(filters.date_to || '');

    // Apply client-side filters
    const filteredActivities = useMemo(() => {
        let filtered = [...activities];

        if (logNameFilter) {
            filtered = filtered.filter((activity) => activity.log_name === logNameFilter);
        }

        if (eventFilter) {
            filtered = filtered.filter((activity) => activity.event && activity.event.toLowerCase().includes(eventFilter.toLowerCase()));
        }

        if (dateFromFilter) {
            filtered = filtered.filter((activity) => new Date(activity.created_at) >= new Date(dateFromFilter));
        }

        if (dateToFilter) {
            filtered = filtered.filter((activity) => new Date(activity.created_at) <= new Date(dateToFilter));
        }

        return filtered;
    }, [activities, logNameFilter, eventFilter, dateFromFilter, dateToFilter]);

    const renderDescription = (activity: Activity) => {
        if (activity.translation) {
            return t(activity.translation.key, activity.translation.replacements as never);
        }

        if (activity.translated_description) {
            return activity.translated_description;
        }

        return t(activity.description as never);
    };

    const columns: ColumnDef<Activity>[] = useMemo(
        () => [
            {
                accessorKey: 'log_name',
                header: t('admin.activities.table.log_name'),
                cell: ({ row }) => <Badge variant="outline">{row.original.log_name}</Badge>,
            },
            {
                accessorKey: 'translated_description',
                header: t('admin.activities.table.description'),
                cell: ({ row }) => <div className="max-w-md truncate">{String(renderDescription(row.original))}</div>,
            },
            {
                accessorKey: 'event',
                header: t('admin.activities.table.event'),
                cell: ({ row }) => <Badge variant="secondary">{row.original.event}</Badge>,
            },
            {
                accessorKey: 'causer',
                header: t('admin.activities.table.causer'),
                cell: ({ row }) => (row.original.causer ? row.original.causer.name : 'System'),
            },
            {
                accessorKey: 'created_at',
                header: t('common.labels.created_at'),
                cell: ({ row }) => format(new Date(row.original.created_at), 'MMM d, yyyy HH:mm'),
            },
        ],
        [t],
    );

    return (
        <AdminLayout>
            <Head title={t('admin.activities.title')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.activities.title')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.activities.description')}</p>
                    </div>

                    <div className="rounded-lg border border-border bg-card p-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">{t('admin.activities.filters.log_name')}</label>
                                <Select
                                    value={logNameFilter || '__all__'}
                                    onValueChange={(value) => setLogNameFilter(value === '__all__' ? '' : value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('admin.activities.filters.all_logs')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="__all__">{t('admin.activities.filters.all_logs')}</SelectItem>
                                        {logNames.map((name) => (
                                            <SelectItem key={name} value={name}>
                                                {name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">{t('admin.activities.filters.event')}</label>
                                <Input
                                    placeholder={t('admin.activities.filters.event_placeholder')}
                                    value={eventFilter}
                                    onChange={(e) => setEventFilter(e.target.value)}
                                />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">{t('admin.activities.filters.date_from')}</label>
                                <Input type="date" value={dateFromFilter} onChange={(e) => setDateFromFilter(e.target.value)} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">{t('admin.activities.filters.date_to')}</label>
                                <Input type="date" value={dateToFilter} onChange={(e) => setDateToFilter(e.target.value)} />
                            </div>
                        </div>
                    </div>

                    <PaginatedDataTable
                        columns={columns}
                        data={filteredActivities}
                        onRowClick={(activity) => setSelectedActivity(activity)}
                        enableRowSelection={false}
                        searchColumnIds={['translated_description', 'event']}
                        searchPlaceholder={t('admin.activities.search_placeholder', 'Search activities...')}
                    />
                </div>

                <ActivityDetailsSheet activity={selectedActivity} onClose={() => setSelectedActivity(null)} />
            </div>
        </AdminLayout>
    );
}
