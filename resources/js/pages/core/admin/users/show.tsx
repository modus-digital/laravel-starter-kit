import { destroy, edit, forceDelete, index, restore, show } from '@/routes/admin/users';
import { ConfirmDialog } from '@/shared/components/confirm-dialog';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui/tabs';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, Edit, RotateCcw, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type User = {
    id: string;
    name: string;
    email: string;
    phone?: string;
    status: string;
    provider?: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    roles?: Array<{ name: string }>;
    two_factor_confirmed_at?: string;
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
    user: User;
    activities: Activity[];
};

export default function Show() {
    const { user, activities } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
    const [forceDeleteDialogOpen, setForceDeleteDialogOpen] = useState<boolean>(false);

    const renderActivityDescription = (activity: Activity) => {
        if (activity.translation) {
            return t(activity.translation.key, activity.translation.replacements as never);
        }

        if (activity.translated_description) {
            return activity.translated_description;
        }

        return t(activity.description as never);
    };

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.users.navigation_label', 'Users'),
            href: index().url,
        },
        {
            title: user.name,
            href: show({ user: user.id }).url,
        },
    ];

    const handleDelete = () => {
        router.delete(destroy({ user: user.id }).url, {
            preserveScroll: true,
        });
    };

    const handleRestore = () => {
        router.post(
            restore({ user: user.id }).url,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleForceDelete = () => {
        router.delete(forceDelete({ user: user.id }).url, {
            preserveScroll: true,
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'default';
            case 'inactive':
                return 'destructive';
            case 'suspended':
                return 'secondary';
            default:
                return 'outline';
        }
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={user.name} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/admin/users">
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold">{user.name}</h1>
                            <p className="text-sm text-muted-foreground">{user.email}</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {!user.deleted_at && (
                            <>
                                <Link href={edit({ user: user.id }).url}>
                                    <Button variant="outline">
                                        <Edit className="mr-2 h-4 w-4" />
                                        {t('admin.users.edit', 'Edit')}
                                    </Button>
                                </Link>
                                <Button variant="destructive" onClick={() => setDeleteDialogOpen(true)}>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    {t('admin.users.delete', 'Delete')}
                                </Button>
                            </>
                        )}
                        {user.deleted_at && (
                            <>
                                <Button variant="outline" onClick={handleRestore}>
                                    <RotateCcw className="mr-2 h-4 w-4" />
                                    {t('admin.users.restore', 'Restore')}
                                </Button>
                                <Button variant="destructive" onClick={() => setForceDeleteDialogOpen(true)}>
                                    {t('admin.users.force_delete', 'Force Delete')}
                                </Button>
                            </>
                        )}
                    </div>
                </div>

                <Tabs defaultValue="details" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="details">{t('admin.users.details', 'Details')}</TabsTrigger>
                        <TabsTrigger value="activities">{t('admin.users.activities', 'Activities')}</TabsTrigger>
                    </TabsList>

                    <TabsContent value="details">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="flex flex-col gap-4">
                                <div className="rounded-lg border bg-card p-6">
                                    <h2 className="mb-4 text-lg font-semibold">{t('admin.users.details', 'Details')}</h2>
                                    <dl className="grid grid-cols-[auto_1fr] gap-x-8 gap-y-3">
                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.name')}</dt>
                                        <dd className="text-sm">{user.name}</dd>

                                        <dt className="text-sm font-medium text-muted-foreground">{t('admin.users.email', 'Email')}</dt>
                                        <dd className="text-sm">{user.email}</dd>

                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.phone')}</dt>
                                        <dd className="text-sm">{user.phone ?? t('admin.users.phone_not_set', 'Not set')}</dd>

                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.status')}</dt>
                                        <dd className="text-sm">
                                            <Badge variant={getStatusColor(user.status)}>{user.status}</Badge>
                                        </dd>
                                    </dl>
                                </div>

                                <div className="rounded-lg border bg-card p-6">
                                    <h2 className="mb-4 text-lg font-semibold">{t('admin.users.metadata', 'Metadata')}</h2>
                                    <dl className="grid grid-cols-[auto_1fr] gap-x-8 gap-y-3">
                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.created_at')}</dt>
                                        <dd className="text-sm">{format(new Date(user.created_at), 'EEEE d MMMM yyyy, HH:mm')}</dd>

                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.updated_at')}</dt>
                                        <dd className="text-sm">{format(new Date(user.updated_at), 'EEEE d MMMM yyyy, HH:mm')}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div className="self-start rounded-lg border bg-card p-6">
                                <h2 className="mb-4 text-lg font-semibold">{t('admin.users.security', 'Security')}</h2>
                                <dl className="grid grid-cols-[auto_1fr] gap-x-8 gap-y-3">
                                    <dt className="text-sm font-medium text-muted-foreground">{t('admin.users.security.provider', 'Provider')}</dt>
                                    <dd className="text-sm">{user.provider}</dd>

                                    <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.role')}</dt>
                                    <dd className="text-sm">
                                        {user.roles?.length && user.roles.length > 0 ? user.roles?.map((role) => role.name).join(', ') : '-'}
                                    </dd>

                                    <dt className="text-sm font-medium text-muted-foreground">
                                        {t('admin.users.security.two_factor_enabled', 'Two Factor Enabled')}
                                    </dt>
                                    <dd className="text-sm">
                                        {user.two_factor_confirmed_at
                                            ? t('admin.users.security.two_factor_enabled_yes', 'Yes')
                                            : t('admin.users.security.two_factor_enabled_no', 'No')}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </TabsContent>

                    <TabsContent value="activities" className="space-y-4">
                        <div className="overflow-hidden rounded-lg border bg-card">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('admin.users.activity.description', 'Description')}</TableHead>
                                        <TableHead>{t('admin.users.activity.causer', 'Causer')}</TableHead>
                                        <TableHead>{t('common.labels.created_at')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {activities.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={3} className="h-24 text-center text-sm text-muted-foreground">
                                                {t('admin.users.no_activities', 'No activities found.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        activities.map((activity) => (
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
                        </div>
                    </TabsContent>
                </Tabs>

                {/* Confirmation Modals */}
                <ConfirmDialog
                    open={deleteDialogOpen}
                    onOpenChange={setDeleteDialogOpen}
                    onConfirm={handleDelete}
                    title={t('admin.users.confirm_delete_title', 'Delete User')}
                    description={t('admin.users.confirm_delete', 'Are you sure you want to delete this user?')}
                    confirmText={t('admin.users.delete', 'Delete')}
                    cancelText={t('common.actions.cancel')}
                    variant="destructive"
                />

                <ConfirmDialog
                    open={forceDeleteDialogOpen}
                    onOpenChange={setForceDeleteDialogOpen}
                    onConfirm={handleForceDelete}
                    title={t('admin.users.confirm_force_delete_title', 'Permanently Delete User')}
                    description={t(
                        'admin.users.confirm_force_delete',
                        'Are you sure you want to permanently delete this user? This action cannot be undone.',
                    )}
                    confirmText={t('admin.users.force_delete', 'Force Delete')}
                    cancelText={t('common.actions.cancel')}
                    variant="destructive"
                />
            </div>
        </AdminLayout>
    );
}
