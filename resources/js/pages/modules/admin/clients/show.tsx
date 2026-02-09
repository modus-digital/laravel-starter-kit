import { addUser, destroy, edit, forceDelete, index, show } from '@/routes/admin/clients';
import { ConfirmDialog } from '@/shared/components/confirm-dialog';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui/tabs';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type AvailableUser, type Client, type ClientActivity, type ClientUser, type PaginatedData, type RoleOption } from '@/types/admin/clients';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, Edit, Plus, RotateCcw, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type PageProps = SharedData & {
    client: Client;
    users: PaginatedData<ClientUser>;
    activities: PaginatedData<ClientActivity>;
    roles: RoleOption[];
    availableUsers: AvailableUser[];
    statuses: Record<string, string>;
};

export default function Show() {
    const { client, users, activities, roles, availableUsers, statuses } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [forceDeleteDialogOpen, setForceDeleteDialogOpen] = useState(false);
    const [addUserModalOpen, setAddUserModalOpen] = useState(false);
    const [addUserModalTab, setAddUserModalTab] = useState<'existing' | 'new'>('existing');
    const [usersSearch, setUsersSearch] = useState('');
    const [removeUserDialogOpen, setRemoveUserDialogOpen] = useState(false);
    const [userToRemove, setUserToRemove] = useState<User | null>(null);

    const addUserForm = useForm({
        user_id: '',
        role_id: '',
    });
    const {
        data: addUserData,
        setData: setAddUserData,
        post: addUserPost,
        processing: addUserProcessing,
        errors: addUserErrors,
        reset: addUserReset,
    } = addUserForm;

    const createUserForm = useForm({
        name: '',
        email: '',
        password: '',
        role_id: '',
        status: 'active',
    });
    const {
        data: createUserData,
        setData: setCreateUserData,
        post: createUserPost,
        processing: createUserProcessing,
        errors: createUserErrors,
        reset: createUserReset,
    } = createUserForm;

    const filteredUsers = users.data.filter(
        (u) =>
            !usersSearch.trim() ||
            u.name.toLowerCase().includes(usersSearch.toLowerCase()) ||
            u.email.toLowerCase().includes(usersSearch.toLowerCase()),
    );

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
        router.post(
            `/admin/clients/${client.id}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
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

    const handleAddUserSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        addUserPost(addUser({ client: client.id }).url, {
            preserveScroll: true,
            onSuccess: () => {
                setAddUserModalOpen(false);
                setAddUserModalTab('existing');
                addUserReset();
            },
        });
    };

    const handleCreateUserSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createUserPost(`/admin/clients/${client.id}/users`, {
            preserveScroll: true,
            onSuccess: () => {
                setAddUserModalOpen(false);
                setAddUserModalTab('existing');
                createUserReset();
            },
        });
    };

    const handleAddUserModalOpenChange = (open: boolean) => {
        setAddUserModalOpen(open);
        if (!open) {
            setAddUserModalTab('existing');
            addUserReset();
            createUserReset();
        }
    };

    const handleRoleChange = (user: User, roleId: string) => {
        const numRoleId = Number(roleId);
        if (Number.isNaN(numRoleId)) return;
        router.put(`/admin/clients/${client.id}/users/${user.id}/role`, { role_id: numRoleId }, { preserveScroll: true });
    };

    const handleRemoveUser = () => {
        if (!userToRemove) return;
        router.delete(`/admin/clients/${client.id}/users/${userToRemove.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setRemoveUserDialogOpen(false);
                setUserToRemove(null);
            },
        });
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
                                        {t('common.actions.edit')}
                                    </Button>
                                </Link>
                                <Button variant="destructive" onClick={() => setDeleteDialogOpen(true)}>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    {t('common.actions.delete')}
                                </Button>
                            </>
                        )}
                        {client.deleted_at && (
                            <>
                                <Button variant="outline" onClick={handleRestore}>
                                    <RotateCcw className="mr-2 h-4 w-4" />
                                    {t('common.actions.restore')}
                                </Button>
                                <Button variant="destructive" onClick={() => setForceDeleteDialogOpen(true)}>
                                    {t('admin.clients.force_delete')}
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
                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.name')}</dt>
                                        <dd className="mt-1.5 text-sm">{client.name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.status')}</dt>
                                        <dd className="mt-1.5">
                                            <Badge variant={getStatusColor(client.status)}>{client.status}</Badge>
                                        </dd>
                                    </div>
                                    {client.contact_name && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">
                                                {t('admin.clients.form.contact_information.contact_name', 'Contact Name')}
                                            </dt>
                                            <dd className="mt-1.5 text-sm">{client.contact_name}</dd>
                                        </div>
                                    )}
                                    {client.contact_email && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">
                                                {t('admin.clients.form.contact_information.contact_email', 'Contact Email')}
                                            </dt>
                                            <dd className="mt-1.5 text-sm">{client.contact_email}</dd>
                                        </div>
                                    )}
                                    {client.contact_phone && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">
                                                {t('admin.clients.form.contact_information.contact_phone', 'Contact Phone')}
                                            </dt>
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
                                                    <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.address')}</dt>
                                                    <dd className="mt-1.5 text-sm">{client.address}</dd>
                                                </div>
                                            )}
                                            <div className="grid grid-cols-2 gap-x-6">
                                                {client.city && (
                                                    <div>
                                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.city')}</dt>
                                                        <dd className="mt-1.5 text-sm">{client.city}</dd>
                                                    </div>
                                                )}
                                                {client.postal_code && (
                                                    <div>
                                                        <dt className="text-sm font-medium text-muted-foreground">
                                                            {t('common.labels.postal_code')}
                                                        </dt>
                                                        <dd className="mt-1.5 text-sm">{client.postal_code}</dd>
                                                    </div>
                                                )}
                                            </div>
                                            {client.country && (
                                                <div>
                                                    <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.country')}</dt>
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
                                                <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.created_at')}</dt>
                                                <dd className="mt-1.5 text-sm">{format(new Date(client.created_at), 'PPp')}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.updated_at')}</dt>
                                                <dd className="mt-1.5 text-sm">{format(new Date(client.updated_at), 'PPp')}</dd>
                                            </div>
                                        </div>
                                        {client.deleted_at && (
                                            <div>
                                                <dt className="text-sm font-medium text-muted-foreground">
                                                    {t('admin.clients.deleted_at', 'Deleted At')}
                                                </dt>
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
                            <div className="my-2 flex w-full items-center justify-between gap-2 p-2">
                                <Input
                                    type="text"
                                    placeholder={t('common.labels.search', 'Search')}
                                    value={usersSearch}
                                    onChange={(e) => setUsersSearch(e.target.value)}
                                    className="max-w-sm"
                                />
                                <Button variant="outline" onClick={() => setAddUserModalOpen(true)}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('admin.clients.add_user', 'Add User')}
                                </Button>
                            </div>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('common.labels.name')}</TableHead>
                                        <TableHead>{t('common.labels.email')}</TableHead>
                                        <TableHead>{t('admin.clients.role', 'Role')}</TableHead>
                                        <TableHead>{t('common.labels.status')}</TableHead>
                                        <TableHead className="text-right">{t('common.labels.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredUsers.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="h-24 text-center text-sm text-muted-foreground">
                                                {users.data.length === 0
                                                    ? t('admin.clients.no_users', 'No users found.')
                                                    : t('common.search.no_results', 'No results.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        filteredUsers.map((user) => {
                                            const currentRoleId = user.role ? roles.find((r) => r.name === user.role)?.id : undefined;
                                            return (
                                                <TableRow key={user.id}>
                                                    <TableCell className="font-medium">{user.name}</TableCell>
                                                    <TableCell>{user.email}</TableCell>
                                                    <TableCell>
                                                        <Select
                                                            value={currentRoleId != null ? String(currentRoleId) : ''}
                                                            onValueChange={(value) => handleRoleChange(user, value)}
                                                        >
                                                            <SelectTrigger className="w-[180px]">
                                                                <SelectValue placeholder={t('admin.clients.select_role', 'Select role')} />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                {roles.map((role) => (
                                                                    <SelectItem key={role.id} value={String(role.id)}>
                                                                        {role.label}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectContent>
                                                        </Select>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant={getStatusColor(user.status)}>{user.status}</Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                setUserToRemove(user);
                                                                setRemoveUserDialogOpen(true);
                                                            }}
                                                        >
                                                            <Trash2 className="h-4 w-4 text-destructive" />
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })
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
                                                link.active ? 'bg-primary text-primary-foreground' : 'bg-background text-foreground hover:bg-muted'
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
                                        <TableHead>{t('common.labels.description')}</TableHead>
                                        <TableHead>{t('admin.activities.table.causer')}</TableHead>
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
                    </TabsContent>
                </Tabs>

                {/* Confirmation Modals */}
                <ConfirmDialog
                    open={deleteDialogOpen}
                    onOpenChange={setDeleteDialogOpen}
                    onConfirm={handleDelete}
                    title={t('admin.clients.confirm_delete_title')}
                    description={t('common.messages.confirm_delete')}
                    confirmText={t('common.actions.delete')}
                    cancelText={t('common.actions.cancel')}
                    variant="destructive"
                />

                <ConfirmDialog
                    open={forceDeleteDialogOpen}
                    onOpenChange={setForceDeleteDialogOpen}
                    onConfirm={handleForceDelete}
                    title={t('admin.clients.confirm_force_delete_title')}
                    description={t('admin.clients.confirm_force_delete')}
                    confirmText={t('admin.clients.force_delete')}
                    cancelText={t('common.actions.cancel')}
                    variant="destructive"
                />

                <ConfirmDialog
                    open={removeUserDialogOpen}
                    onOpenChange={setRemoveUserDialogOpen}
                    onConfirm={handleRemoveUser}
                    title={t('admin.clients.confirm_remove_user_title', 'Remove User')}
                    description={t('admin.clients.confirm_remove_user', 'Are you sure you want to remove this user from the client?')}
                    confirmText={t('common.actions.delete')}
                    cancelText={t('common.actions.cancel')}
                    variant="destructive"
                />

                <Dialog open={addUserModalOpen} onOpenChange={handleAddUserModalOpenChange}>
                    <DialogContent className="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>{t('admin.clients.add_user', 'Add User')}</DialogTitle>
                        </DialogHeader>
                        <Tabs value={addUserModalTab} onValueChange={(v) => setAddUserModalTab(v as 'existing' | 'new')} className="w-full">
                            <TabsList className="grid w-full grid-cols-2">
                                <TabsTrigger value="existing">{t('admin.clients.add_existing_user', 'Existing user')}</TabsTrigger>
                                <TabsTrigger value="new">{t('admin.clients.create_new_user', 'New user')}</TabsTrigger>
                            </TabsList>
                            <TabsContent value="existing" className="mt-4 space-y-4">
                                <form onSubmit={handleAddUserSubmit} className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="add-user-id">{t('admin.clients.select_user', 'Select user')}</Label>
                                        <Select value={addUserData.user_id} onValueChange={(value) => setAddUserData('user_id', value)} required>
                                            <SelectTrigger id="add-user-id" className="w-full">
                                                <SelectValue placeholder={t('admin.clients.select_user_placeholder', 'Choose a user')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableUsers.map((u) => (
                                                    <SelectItem key={u.id} value={u.id}>
                                                        {u.name} ({u.email})
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {addUserErrors.user_id && <p className="text-sm text-destructive">{addUserErrors.user_id}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="add-role-id">{t('admin.clients.role', 'Role')}</Label>
                                        <Select
                                            value={addUserData.role_id ? String(addUserData.role_id) : ''}
                                            onValueChange={(value) => setAddUserData('role_id', value === '' ? '' : value)}
                                        >
                                            <SelectTrigger id="add-role-id" className="w-full">
                                                <SelectValue placeholder={t('admin.clients.select_role', 'Select role')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roles.map((role) => (
                                                    <SelectItem key={role.id} value={String(role.id)}>
                                                        {role.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {addUserErrors.role_id && <p className="text-sm text-destructive">{addUserErrors.role_id}</p>}
                                    </div>
                                    <DialogFooter>
                                        <Button type="button" variant="outline" onClick={() => setAddUserModalOpen(false)}>
                                            {t('common.actions.cancel')}
                                        </Button>
                                        <Button type="submit" disabled={addUserProcessing}>
                                            {t('common.actions.add', 'Add')}
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </TabsContent>
                            <TabsContent value="new" className="mt-4 space-y-4">
                                <form onSubmit={handleCreateUserSubmit} className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="new-user-name">{t('common.labels.name')}</Label>
                                        <Input
                                            id="new-user-name"
                                            value={createUserData.name}
                                            onChange={(e) => setCreateUserData('name', e.target.value)}
                                            placeholder={t('common.labels.name')}
                                            required
                                        />
                                        {createUserErrors.name && <p className="text-sm text-destructive">{createUserErrors.name}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="new-user-email">{t('common.labels.email')}</Label>
                                        <Input
                                            id="new-user-email"
                                            type="email"
                                            value={createUserData.email}
                                            onChange={(e) => setCreateUserData('email', e.target.value)}
                                            placeholder={t('common.labels.email')}
                                            required
                                        />
                                        {createUserErrors.email && <p className="text-sm text-destructive">{createUserErrors.email}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="new-user-password">{t('common.labels.password')}</Label>
                                        <Input
                                            id="new-user-password"
                                            type="password"
                                            value={createUserData.password}
                                            onChange={(e) => setCreateUserData('password', e.target.value)}
                                            placeholder={t('common.labels.password')}
                                            required
                                        />
                                        {createUserErrors.password && <p className="text-sm text-destructive">{createUserErrors.password}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="new-user-role-id">{t('admin.clients.role', 'Role')}</Label>
                                        <Select
                                            value={createUserData.role_id ? String(createUserData.role_id) : ''}
                                            onValueChange={(value) => setCreateUserData('role_id', value === '' ? '' : value)}
                                        >
                                            <SelectTrigger id="new-user-role-id" className="w-full">
                                                <SelectValue placeholder={t('admin.clients.select_role', 'Select role')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roles.map((role) => (
                                                    <SelectItem key={role.id} value={String(role.id)}>
                                                        {role.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {createUserErrors.role_id && <p className="text-sm text-destructive">{createUserErrors.role_id}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="new-user-status">{t('common.labels.status')}</Label>
                                        <Select value={createUserData.status} onValueChange={(value) => setCreateUserData('status', value)}>
                                            <SelectTrigger id="new-user-status" className="w-full">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(statuses).map(([value, label]) => (
                                                    <SelectItem key={value} value={value}>
                                                        {label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {createUserErrors.status && <p className="text-sm text-destructive">{createUserErrors.status}</p>}
                                    </div>
                                    <DialogFooter>
                                        <Button type="button" variant="outline" onClick={() => setAddUserModalOpen(false)}>
                                            {t('common.actions.cancel')}
                                        </Button>
                                        <Button type="submit" disabled={createUserProcessing}>
                                            {t('admin.clients.create_and_add_user', 'Create & add')}
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </TabsContent>
                        </Tabs>
                    </DialogContent>
                </Dialog>
            </div>
        </AdminLayout>
    );
}
