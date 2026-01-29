import AdminLayout from '@/shared/layouts/admin/layout';
import { Button } from '@/shared/components/ui/button';
import { Badge } from '@/shared/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { type SharedData } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Edit, Trash2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { format } from 'date-fns';

type Permission = {
    id: string;
    name: string;
};

type Role = {
    id: string;
    name: string;
    guard_name: string;
    icon?: string;
    color?: string;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
};

type Activity = {
    id: string;
    description: string;
    translated_description?: string;
    translation?: {
        key: string;
        replacements: Record<string, string>;
    };
    causer: {
        id: number;
        name: string;
        email: string;
    } | null;
    created_at: string;
};

type PageProps = SharedData & {
    role: Role;
    activities: {
        data: Activity[];
    };
};

export default function Show({ role, activities }: PageProps) {
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

    const handleDelete = () => {
        if (confirm(t('admin.roles.confirm_delete'))) {
            router.delete(`/admin/roles/${role.id}`);
        }
    };

    return (
        <AdminLayout>
            <Head title={role.name} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => router.visit('/admin/roles')}
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-2xl font-semibold">{role.name}</h1>
                            <p className="text-sm text-muted-foreground">
                                {t('admin.roles.view_description')}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={() => router.visit(`/admin/roles/${role.id}/edit`)}
                        >
                            <Edit className="mr-2 h-4 w-4" />
                            {t('common.actions.edit')}
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                        >
                            <Trash2 className="mr-2 h-4 w-4" />
                            {t('common.actions.delete')}
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('admin.roles.details')}</CardTitle>
                            <CardDescription>{t('admin.roles.details_description')}</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">{t('admin.roles.form.name')}</p>
                                <p className="text-sm">{role.name}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">{t('admin.roles.form.guard_name')}</p>
                                <p className="text-sm">{role.guard_name}</p>
                            </div>
                            {role.icon && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">{t('admin.roles.form.icon')}</p>
                                    <p className="text-sm">{role.icon}</p>
                                </div>
                            )}
                            {role.color && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">{t('admin.roles.form.color')}</p>
                                    <div className="flex items-center gap-2">
                                        <div
                                            className="h-6 w-6 rounded border"
                                            style={{ backgroundColor: role.color }}
                                        />
                                        <p className="text-sm">{role.color}</p>
                                    </div>
                                </div>
                            )}
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">{t('admin.roles.created_at')}</p>
                                <p className="text-sm">{format(new Date(role.created_at), 'MMM d, yyyy HH:mm')}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">{t('admin.roles.updated_at')}</p>
                                <p className="text-sm">{format(new Date(role.updated_at), 'MMM d, yyyy HH:mm')}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('admin.roles.permissions')}</CardTitle>
                            <CardDescription>
                                {t('admin.roles.permissions_count', { count: role.permissions.length })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {role.permissions.length === 0 ? (
                                <p className="text-sm text-muted-foreground text-center py-8">
                                    {t('admin.roles.no_permissions')}
                                </p>
                            ) : (
                                <div className="flex flex-wrap gap-2">
                                    {role.permissions.map((permission) => (
                                        <Badge key={permission.id} variant="secondary">
                                            {permission.name}
                                        </Badge>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {activities.data.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('admin.roles.recent_activity')}</CardTitle>
                            <CardDescription>{t('admin.roles.recent_activity_description')}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {activities.data.map((activity) => (
                                    <div key={activity.id} className="flex items-start space-x-4 text-sm">
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {String(renderActivityDescription(activity))}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {activity.causer ? activity.causer.name : 'System'} •{' '}
                                                {format(new Date(activity.created_at), 'MMM d, yyyy HH:mm')}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
            </div>
        </AdminLayout>
    );
}
