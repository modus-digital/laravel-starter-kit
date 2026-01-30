import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import { ConfirmDialog } from '@/shared/components/confirm-dialog';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Edit, Trash2, RotateCcw } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { format } from 'date-fns';
import { useState } from 'react';
import { destroy, edit, forceDelete, index, restore, show } from '@/routes/admin/clients';

type Client = {
    id: string;
    name: string;
    contact_name?: string;
    contact_email?: string;
    contact_phone?: string;
    address?: string;
    postal_code?: string;
    city?: string;
    country?: string;
    status: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
};

type User = {
    id: string;
    name: string;
    email: string;
    status: string;
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
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    activities: {
        data: Activity[];
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
};

export default function Show() {
    const { client, users, activities } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [forceDeleteDialogOpen, setForceDeleteDialogOpen] = useState(false);

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
            title: t('admin.clients.navigation_label', 'Clients'),
            href: index().url,
        },
        {
            title: client.name,
            href: show({ client: client.id }).url,
        },
    ];

    const handleDelete = () => {
        router.delete(destroy({ client: client.id }).url, {
            preserveScroll: true,
        });
    };

    const handleRestore = () => {
        router.post(`/admin/clients/${client.id}/restore`, {}, {
            preserveScroll: true,
        });
    };

    const handleForceDelete = () => {
        router.delete(forceDelete({ client: client.id }).url, {
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
            <Head title={client.name} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={index().url}>
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold">{client.name}</h1>
                            {client.contact_email && <p className="text-sm text-muted-foreground">{client.contact_email}</p>}
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {!client.deleted_at && (
                            <>
                                <Link href={edit({ client: client.id }).url}>
                                    <Button variant="outline">
                                        <Edit className="mr-2 h-4 w-4" />
                                        {t('admin.clients.edit', 'Edit')}
                                    </Button>
                                </Link>
                                <Button variant="destructive" onClick={() => setDeleteDialogOpen(true)}>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    {t('admin.clients.delete', 'Delete')}
                                </Button>
                            </>
                        )}
                        {client.deleted_at && (
                            <>
                                <Button variant="outline" onClick={handleRestore}>
                                    <RotateCcw className="mr-2 h-4 w-4" />
                                    {t('admin.clients.restore', 'Restore')}
                                </Button>
                                <Button variant="destructive" onClick={() => setForceDeleteDialogOpen(true)}>
                                    {t('admin.clients.force_delete', 'Force Delete')}
                                </Button>
                            </>
                        )}
                    </div>
                </div>

                <Tabs defaultValue="details" className="space-y-6">
                    <TabsList>
                        <TabsTrigger value="details">{t('admin.clients.details', 'Details')}</TabsTrigger>
                        <TabsTrigger value="users">{t('admin.clients.users', 'Users')}</TabsTrigger>
                        <TabsTrigger value="activities">{t('admin.clients.activities', 'Activities')}</TabsTrigger>
                    </TabsList>

                    <TabsContent value="details">
                        <div className="grid gap-6 md:grid-cols-2">
                            {/* Left Column */}
                            <div className="rounded-lg border bg-card p-6">
                                <dl className="space-y-6">
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.base.name', 'Name')}</dt>
                                        <dd className="mt-1.5 text-sm">{client.name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.base.status', 'Status')}</dt>
                                        <dd className="mt-1.5">
                                            <Badge variant={getStatusColor(client.status)}>{client.status}</Badge>
                                        </dd>
                                    </div>
                                    {client.contact_name && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.contact_information.contact_name', 'Contact Name')}</dt>
                                            <dd className="mt-1.5 text-sm">{client.contact_name}</dd>
                                        </div>
                                    )}
                                    {client.contact_email && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.contact_information.contact_email', 'Contact Email')}</dt>
                                            <dd className="mt-1.5 text-sm">{client.contact_email}</dd>
                                        </div>
                                    )}
                                    {client.contact_phone && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.contact_information.contact_phone', 'Contact Phone')}</dt>
                                            <dd className="mt-1.5 text-sm">{client.contact_phone}</dd>
                                        </div>
                                    )}
                                </dl>
                            </div>

                            {/* Right Column */}
                            <div className="space-y-6">
                                {(client.address || client.city || client.country) && (
                                    <div className="rounded-lg border bg-card p-6">
                                        <dl className="space-y-6">
                                            {client.address && (
                                                <div>
                                                    <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.location.address', 'Address')}</dt>
                                                    <dd className="mt-1.5 text-sm">{client.address}</dd>
                                                </div>
                                            )}
                                            <div className="grid grid-cols-2 gap-x-6">
                                                {client.city && (
                                                    <div>
                                                        <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.location.city', 'City')}</dt>
                                                        <dd className="mt-1.5 text-sm">{client.city}</dd>
                                                    </div>
                                                )}
                                                {client.postal_code && (
                                                    <div>
                                                        <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.location.postal_code', 'Postal Code')}</dt>
                                                        <dd className="mt-1.5 text-sm">{client.postal_code}</dd>
                                                    </div>
                                                )}
                                            </div>
                                            {client.country && (
                                                <div>
                                                    <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.form.location.country', 'Country')}</dt>
                                                    <dd className="mt-1.5 text-sm">{client.country}</dd>
                                                </div>
                                            )}
                                        </dl>
                                    </div>
                                )}

                                <div className="rounded-lg border bg-card p-6">
                                    <dl className="space-y-6">
                                        <div className="grid grid-cols-2 gap-x-6">
                                            <div>
                                                <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.created_at', 'Created At')}</dt>
                                                <dd className="mt-1.5 text-sm">{format(new Date(client.created_at), 'PPp')}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.updated_at', 'Updated At')}</dt>
                                                <dd className="mt-1.5 text-sm">{format(new Date(client.updated_at), 'PPp')}</dd>
                                            </div>
                                        </div>
                                        {client.deleted_at && (
                                            <div>
                                                <dt className="text-sm font-medium text-muted-foreground">{t('admin.clients.deleted_at', 'Deleted At')}</dt>
                                                <dd className="mt-1.5 text-sm">{format(new Date(client.deleted_at), 'PPp')}</dd>
                                            </div>
                                        )}
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </TabsContent>

                    <TabsContent value="users" className="space-y-4">
                        <div className="overflow-hidden rounded-lg border bg-card">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('admin.users.table.name', 'Name')}</TableHead>
                                        <TableHead>{t('admin.users.table.email', 'Email')}</TableHead>
                                        <TableHead>{t('admin.users.table.status', 'Status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {users.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={3} className="h-24 text-center text-sm text-muted-foreground">
                                                {t('admin.clients.no_users', 'No users found.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        users.data.map((user) => (
                                            <TableRow key={user.id}>
                                                <TableCell className="font-medium">{user.name}</TableCell>
                                                <TableCell>{user.email}</TableCell>
                                                <TableCell>
                                                    <Badge variant={getStatusColor(user.status)}>{user.status}</Badge>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>

                            {users.last_page > 1 && (
                                <div className="flex items-center justify-end gap-2 border-t p-4">
                                    {users.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`rounded px-3 py-1 text-sm ${
                                                link.active
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-background text-foreground hover:bg-muted'
                                            } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                        >
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </div>
                    </TabsContent>

                    <TabsContent value="activities" className="space-y-4">
                        <div className="overflow-hidden rounded-lg border bg-card">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('admin.clients.activity.description', 'Description')}</TableHead>
                                        <TableHead>{t('admin.clients.activity.causer', 'Causer')}</TableHead>
                                        <TableHead>{t('admin.clients.activity.created_at', 'Created At')}</TableHead>
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
                                                link.active
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-background text-foreground hover:bg-muted'
                                            } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                        >
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </div>
                    </TabsContent>
                </Tabs>

                {/* Confirmation Modals */}
                <ConfirmDialog
                    open={deleteDialogOpen}
                    onOpenChange={setDeleteDialogOpen}
                    onConfirm={handleDelete}
                    title={t('admin.clients.confirm_delete_title', 'Delete Client')}
                    description={t('admin.clients.confirm_delete', 'Are you sure you want to delete this client?')}
                    confirmText={t('admin.clients.delete', 'Delete')}
                    cancelText={t('admin.clients.cancel', 'Cancel')}
                    variant="destructive"
                />

                <ConfirmDialog
                    open={forceDeleteDialogOpen}
                    onOpenChange={setForceDeleteDialogOpen}
                    onConfirm={handleForceDelete}
                    title={t('admin.clients.confirm_force_delete_title', 'Permanently Delete Client')}
                    description={t('admin.clients.confirm_force_delete', 'Are you sure you want to permanently delete this client? This action cannot be undone.')}
                    confirmText={t('admin.clients.force_delete', 'Force Delete')}
                    cancelText={t('admin.clients.cancel', 'Cancel')}
                    variant="destructive"
                />
            </div>
        </AdminLayout>
    );
}
