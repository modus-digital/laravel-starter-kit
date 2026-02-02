import { PaginatedDataTable } from '@/shared/components/paginated-data-table';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Checkbox } from '@/shared/components/ui/checkbox';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/shared/components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Edit, Eye, MoreVertical, Plus, RotateCcw, Trash, Trash2, User } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { bulkDelete, bulkRestore, create, destroy, edit, forceDelete, impersonate, index, restore, show } from '@/routes/admin/users';

type User = {
    id: string;
    name: string;
    email: string;
    phone?: string;
    status: string;
    role?: string;
    created_at: string;
    deleted_at?: string;
};

type PageProps = SharedData & {
    users: User[];
    filters: {
        search?: string;
        status?: string;
        with_trashed?: boolean;
        only_trashed?: boolean;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    roles: Array<{ name: string; label: string }>;
    statuses: Record<string, string>;
};

export default function Index({ users, filters, roles, statuses }: PageProps) {
    const { t } = useTranslation();

    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [withTrashed, setWithTrashed] = useState(filters.with_trashed || false);
    const [onlyTrashed, setOnlyTrashed] = useState(filters.only_trashed || false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.users.navigation_label', 'Users'),
            href: index().url,
        },
    ];

    // Apply client-side filters
    const filteredUsers = useMemo(() => {
        let filtered = [...users];

        if (status) {
            filtered = filtered.filter((user) => user.status === status);
        }

        if (withTrashed && onlyTrashed) {
            filtered = filtered.filter((user) => user.deleted_at);
        } else if (!withTrashed) {
            filtered = filtered.filter((user) => !user.deleted_at);
        }

        return filtered;
    }, [users, status, withTrashed, onlyTrashed]);

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

    const handleDelete = (userId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        if (confirm(t('admin.users.confirm_delete', 'Are you sure you want to delete this user?'))) {
            router.delete(destroy({ user: userId }).url, {
                preserveScroll: true,
            });
        }
    };

    const handleRestore = (userId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        router.post(
            restore({ user: userId }).url,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleForceDelete = (userId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        if (confirm(t('admin.users.confirm_force_delete', 'Are you sure you want to permanently delete this user? This action cannot be undone.'))) {
            router.delete(forceDelete({ user: userId }).url, {
                preserveScroll: true,
            });
        }
    };

    const handleBulkDelete = (selectedUsers: User[]) => {
        const ids = selectedUsers.map((u) => u.id);
        if (confirm(t('admin.users.confirm_bulk_delete', `Are you sure you want to delete ${ids.length} users?`))) {
            router.post(
                bulkDelete().url,
                { ids },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const handleBulkRestore = (selectedUsers: User[]) => {
        const ids = selectedUsers.map((u) => u.id);
        router.post(
            bulkRestore().url,
            { ids },
            {
                preserveScroll: true,
            },
        );
    };

    const columns: ColumnDef<User>[] = useMemo(
        () => [
            {
                id: 'select',
                size: 40,
                header: ({ table }) => (
                    <Checkbox
                        checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                        onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                        aria-label="Select all"
                        data-row-select
                    />
                ),
                cell: ({ row }) => (
                    <Checkbox
                        checked={row.getIsSelected()}
                        onCheckedChange={(value) => row.toggleSelected(!!value)}
                        aria-label="Select row"
                        data-row-select
                    />
                ),
                enableSorting: false,
                enableHiding: false,
            },
            {
                accessorKey: 'name',
                header: t('common.labels.name'),
                cell: ({ row }) => <div className="font-medium">{row.original.name}</div>,
            },
            {
                accessorKey: 'email',
                header: t('common.labels.email'),
                cell: ({ row }) => row.original.email,
            },
            {
                accessorKey: 'role',
                header: t('common.labels.role'),
                cell: ({ row }) =>
                    row.original.role ? (
                        <Badge variant="outline">{t(`enums.rbac.role.${row.original.role}` as never)}</Badge>
                    ) : (
                        <span className="text-muted-foreground">{t('admin.users.table.no_role')}</span>
                    ),
            },
            {
                accessorKey: 'status',
                header: t('common.labels.status'),
                cell: ({ row }) => (
                    <Badge variant={getStatusColor(row.original.status)}>{statuses[row.original.status] || row.original.status}</Badge>
                ),
            },
            {
                accessorKey: 'created_at',
                header: t('common.labels.created_at'),
                cell: ({ row }) => format(new Date(row.original.created_at), 'MMM d, yyyy'),
            },
            {
                id: 'actions',
                size: 50,
                meta: { className: 'text-right' },
                enableHiding: false,
                cell: ({ row }) => {
                    const user = row.original;

                    return (
                        <div data-row-action className="flex justify-end">
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" size="icon">
                                        <MoreVertical className="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            router.visit(show({ user: user.id }).url);
                                        }}
                                    >
                                        <Eye className="mr-2 h-4 w-4" />
                                        {t('common.actions.view')}
                                    </DropdownMenuItem>
                                    {!user.deleted_at && (
                                        <>
                                            <DropdownMenuItem
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    router.post(impersonate({ targetUser: user.id }).url);
                                                }}
                                            >
                                                <User className="mr-2 h-4 w-4" />
                                                {t('admin.users.impersonate', 'Impersonate')}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    router.visit(edit({ user: user.id }).url);
                                                }}
                                            >
                                                <Edit className="mr-2 h-4 w-4" />
                                                {t('common.actions.edit')}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem className="text-destructive" onClick={(e) => handleDelete(user.id, e)}>
                                                <Trash2 className="mr-2 h-4 w-4" />
                                                {t('common.actions.delete')}
                                            </DropdownMenuItem>
                                        </>
                                    )}
                                    {user.deleted_at && (
                                        <>
                                            <DropdownMenuItem onClick={(e) => handleRestore(user.id, e)}>
                                                <RotateCcw className="mr-2 h-4 w-4" />
                                                {t('common.actions.restore')}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem className="text-destructive" onClick={(e) => handleForceDelete(user.id, e)}>
                                                <Trash className="mr-2 h-4 w-4" />
                                                {t('admin.users.force_delete')}
                                            </DropdownMenuItem>
                                        </>
                                    )}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    );
                },
            },
        ],
        [t, statuses, handleDelete, handleRestore, handleForceDelete],
    );

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('admin.users.navigation_label', 'Users')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.users.navigation_label', 'Users')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.users.description', 'Manage system users')}</p>
                    </div>
                    <Link href={create().url}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            {t('admin.users.create', 'Create User')}
                        </Button>
                    </Link>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div className="w-full sm:w-48">
                            <label className="mb-2 block text-sm font-medium">{t('common.labels.status')}</label>
                            <Select value={status || undefined} onValueChange={(value) => setStatus(value || '')}>
                                <SelectTrigger>
                                    <SelectValue placeholder={t('admin.users.all_statuses', 'All Statuses')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(statuses).map(([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="flex items-center gap-2">
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={withTrashed}
                                    onChange={(e) => setWithTrashed(e.target.checked)}
                                    className="rounded border-gray-300"
                                />
                                {t('admin.users.with_trashed', 'With Trashed')}
                            </label>
                            {withTrashed && (
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={onlyTrashed}
                                        onChange={(e) => setOnlyTrashed(e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    {t('admin.users.only_trashed', 'Only Trashed')}
                                </label>
                            )}
                        </div>
                    </div>
                </div>

                <PaginatedDataTable
                    columns={columns}
                    data={filteredUsers}
                    searchColumnIds={['name', 'email']}
                    searchPlaceholder={t('admin.users.search_placeholder', 'Search by name or email...')}
                    onRowClick={(user) => router.visit(show({ user: user.id }).url)}
                    enableRowSelection
                    bulkActionsRender={(selectedUsers) => (
                        <>
                            {onlyTrashed ? (
                                <button
                                    type="button"
                                    className="w-full px-2 py-1.5 text-left text-sm hover:bg-muted"
                                    onClick={() => handleBulkRestore(selectedUsers)}
                                >
                                    {t('admin.users.bulk_restore', 'Restore Selected')}
                                </button>
                            ) : (
                                <button
                                    type="button"
                                    className="w-full px-2 py-1.5 text-left text-sm text-destructive hover:bg-destructive/10"
                                    onClick={() => handleBulkDelete(selectedUsers)}
                                >
                                    {t('admin.users.bulk_delete', 'Delete Selected')}
                                </button>
                            )}
                        </>
                    )}
                />
            </div>
        </AdminLayout>
    );
}
