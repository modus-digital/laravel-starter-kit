import { ActivityDetailsSheet } from '@/components/ui/activity-details-sheet';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/layout';
import { type SharedData } from '@/types';
import { type Activity as ActivityModel } from '@/types/models';
import { Head, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { Activity as ActivityIcon, Key, Shield, Users } from 'lucide-react';
import type { ComponentProps } from 'react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Activity = Omit<ActivityModel, 'properties'> & {
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    translated_description?: string;
    properties: Record<string, unknown> | unknown[];
};

type DashboardStats = {
    total_users: number;
    total_roles: number;
    total_permissions: number;
    total_activities: number;
};

type RoleDistribution = {
    name: string;
    count: number;
};

type PageProps = SharedData & {
    stats: DashboardStats;
    recentActivities: {
        data: Activity[];
    };
    roleDistribution: RoleDistribution[];
};

export default function AdminDashboard() {
    const { stats, recentActivities, roleDistribution } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [selectedActivity, setSelectedActivity] = useState<Activity | null>(null);

    const renderDescription = (activity: Activity) => {
        if (activity.translation) {
            return t(activity.translation.key, activity.translation.replacements as never);
        }

        if (activity.translated_description) {
            return activity.translated_description;
        }

        return t(activity.description as never);
    };

    const statCards = [
        {
            title: t('admin.dashboard.stats.total_users'),
            value: stats.total_users,
            icon: Users,
            description: t('admin.dashboard.stats.users_description'),
        },
        {
            title: t('admin.dashboard.stats.total_roles'),
            value: stats.total_roles,
            icon: Shield,
            description: t('admin.dashboard.stats.roles_description'),
        },
        {
            title: t('admin.dashboard.stats.total_permissions'),
            value: stats.total_permissions,
            icon: Key,
            description: t('admin.dashboard.stats.permissions_description'),
        },
        {
            title: t('admin.dashboard.stats.total_activities'),
            value: stats.total_activities,
            icon: ActivityIcon,
            description: t('admin.dashboard.stats.activities_description'),
        },
    ];

    return (
        <AdminLayout>
            <Head title={t('admin.panel.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {statCards.map((stat, index) => (
                            <Card key={index}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">{stat.title}</CardTitle>
                                    <stat.icon className="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{stat.value}</div>
                                    <p className="text-xs text-muted-foreground">{stat.description}</p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.dashboard.recent_activity')}</CardTitle>
                                <CardDescription>{t('admin.dashboard.recent_activity_description')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentActivities.data.length === 0 ? (
                                    <div className="py-8 text-center text-sm text-muted-foreground">{t('admin.dashboard.no_activities')}</div>
                                ) : (
                                    <div className="relative">
                                        {/* Timeline line */}
                                        <div className="absolute top-0 bottom-0 left-4 w-0.5 bg-border" />
                                        <div className="space-y-4">
                                            {recentActivities.data.map((activity, index) => (
                                                <div
                                                    key={activity.id}
                                                    className="relative flex cursor-pointer items-start gap-4 transition-opacity hover:opacity-80"
                                                    onClick={() => setSelectedActivity(activity)}
                                                >
                                                    {/* Timeline dot */}
                                                    <div className="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-background bg-primary">
                                                        <div className="h-2 w-2 rounded-full bg-primary-foreground" />
                                                    </div>
                                                    {/* Content */}
                                                    <div className="flex-1 space-y-1 pb-4">
                                                        <p className="text-sm leading-none font-medium">{String(renderDescription(activity))}</p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {activity.causer ? activity.causer.name : 'System'} •{' '}
                                                            {format(new Date(activity.created_at), 'MMM d, yyyy HH:mm')}
                                                        </p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.dashboard.role_distribution')}</CardTitle>
                                <CardDescription>{t('admin.dashboard.role_distribution_description')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {roleDistribution.length === 0 ? (
                                    <div className="py-8 text-center text-sm text-muted-foreground">{t('admin.dashboard.no_roles')}</div>
                                ) : (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>{t('admin.dashboard.role_name')}</TableHead>
                                                <TableHead className="text-right">{t('admin.dashboard.user_count')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {roleDistribution.map((role, index) => (
                                                <TableRow key={index}>
                                                    <TableCell className="font-medium">
                                                        <Badge variant="outline">{t(`enums.rbac.role.${role.name}` as never)}</Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">{role.count}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <ActivityDetailsSheet
                activity={selectedActivity as ComponentProps<typeof ActivityDetailsSheet>['activity']}
                onClose={() => setSelectedActivity(null)}
            />
        </AdminLayout>
    );
}
