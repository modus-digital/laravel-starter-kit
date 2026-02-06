import { ActivityDetailsSheet } from '@/shared/components/ui/activity-details-sheet';
import { Badge } from '@/shared/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type Activity, type BreadcrumbItem, type SharedData } from '@/types';
import { Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { BarChart3, FileText, Shield, Users } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

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
    recentActivities: { data: Activity[] };
    roleDistribution: RoleDistribution[];
};

export default function AdminDashboard({ stats, recentActivities, roleDistribution }: PageProps) {
    const { t } = useTranslation();
    const [selectedActivity, setSelectedActivity] = useState<Activity | null>(null);
    const [isSheetOpen, setIsSheetOpen] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.dashboard.title', 'Dashboard'),
            href: '/admin',
        },
    ];

    const statCards = useMemo(
        () => [
            {
                title: t('admin.dashboard.total_users', 'Total Users'),
                value: stats.total_users,
                icon: Users,
                color: 'text-blue-600',
                bgColor: 'bg-blue-100',
            },
            {
                title: t('admin.dashboard.total_roles', 'Total Roles'),
                value: stats.total_roles,
                icon: Shield,
                color: 'text-green-600',
                bgColor: 'bg-green-100',
            },
            {
                title: t('admin.dashboard.total_permissions', 'Total Permissions'),
                value: stats.total_permissions,
                icon: FileText,
                color: 'text-purple-600',
                bgColor: 'bg-purple-100',
            },
            {
                title: t('admin.dashboard.total_activities', 'Total Activities'),
                value: stats.total_activities,
                icon: BarChart3,
                color: 'text-orange-600',
                bgColor: 'bg-orange-100',
            },
        ],
        [stats, t],
    );

    const handleActivityClick = (activity: Activity) => {
        setSelectedActivity(activity);
        setIsSheetOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('admin.dashboard.title', 'Dashboard')} />

            <div className="space-y-6 px-6 py-4">
                {/* Header */}
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.dashboard.title', 'Dashboard')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.dashboard.description', 'Overview of your application')}</p>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {statCards.map((stat) => {
                        const Icon = stat.icon;
                        return (
                            <Card key={stat.title}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">{stat.title}</CardTitle>
                                    <div className={`rounded-full p-2 ${stat.bgColor}`}>
                                        <Icon className={`h-4 w-4 ${stat.color}`} />
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">{stat.value.toLocaleString()}</div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Role Distribution */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('admin.dashboard.role_distribution', 'Role Distribution')}</CardTitle>
                        <CardDescription>{t('admin.dashboard.role_distribution_desc', 'Number of users per role')}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {roleDistribution.map((role) => (
                                <div key={role.name} className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline">{role.name}</Badge>
                                    </div>
                                    <span className="text-sm font-medium">{role.count} users</span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Recent Activities */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('admin.dashboard.recent_activities', 'Recent Activities')}</CardTitle>
                        <CardDescription>{t('admin.dashboard.recent_activities_desc', 'Latest activity log entries')}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {recentActivities.data.length === 0 ? (
                                <p className="text-sm text-muted-foreground">{t('admin.dashboard.no_activities', 'No recent activities')}</p>
                            ) : (
                                recentActivities.data.map((activity) => (
                                    <div
                                        key={activity.id}
                                        className="flex cursor-pointer items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                        onClick={() => handleActivityClick(activity)}
                                    >
                                        <div className="flex-1">
                                            <p className="text-sm font-medium">{activity.description}</p>
                                            <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                                {activity.causer && <span>{activity.causer.name}</span>}
                                                <span>•</span>
                                                <span>{format(new Date(activity.created_at), 'MMM d, yyyy h:mm a')}</span>
                                            </div>
                                        </div>
                                        <Badge variant="secondary">{activity.log_name}</Badge>
                                    </div>
                                ))
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Activity Details Sheet */}
            <ActivityDetailsSheet activity={selectedActivity} open={isSheetOpen} onOpenChange={setIsSheetOpen} />
        </AdminLayout>
    );
}
