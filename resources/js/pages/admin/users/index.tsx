import { PaginatedDataTable } from '@/components/paginated-data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Edit, Eye, MoreVertical, Plus, RotateCcw, Trash, Trash2, User } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

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
            href: '/admin/users',
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
            router.delete(`/admin/users/${userId}`, {
                preserveScroll: true,
            });
        }
    };

    const handleRestore = (userId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        router.post(
            `/admin/users/${userId}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleForceDelete = (userId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        if (confirm(t('admin.users.confirm_force_delete', 'Are you sure you want to permanently delete this user? This action cannot be undone.'))) {
            router.delete(`/admin/users/${userId}/force`, {
                preserveScroll: true,
            });
        }
    };

    const handleBulkDelete = (selectedUsers: User[]) => {
        const ids = selectedUsers.map((u) => u.id);
        if (confirm(t('admin.users.confirm_bulk_delete', `Are you sure you want to delete ${ids.length} users?`))) {
            router.post(
                '/admin/users/bulk-delete',
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
            '/admin/users/bulk-restore',
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
                header: t('admin.users.table.name', 'Name'),
                cell: ({ row }) => <div className="font-medium">{row.original.name}</div>,
            },
            {
                accessorKey: 'email',
                header: t('admin.users.table.email', 'Email'),
                cell: ({ row }) => row.original.email,
            },
            {
                accessorKey: 'role',
                header: t('admin.users.table.role', 'Role'),
                cell: ({ row }) =>
                    row.original.role ? (
                        <Badge variant="outline">{t(`enums.rbac.role.${row.original.role}` as never)}</Badge>
                    ) : (
                        <span className="text-muted-foreground">{t('admin.users.table.no_role', 'No role')}</span>
                    ),
            },
            {
                accessorKey: 'status',
                header: t('admin.users.table.status', 'Status'),
                cell: ({ row }) => (
                    <Badge variant={getStatusColor(row.original.status)}>{statuses[row.original.status] || row.original.status}</Badge>
                ),
            },
            {
                accessorKey: 'created_at',
                header: t('admin.users.table.created_at', 'Created At'),
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
                                            router.visit(`/admin/users/${user.id}`);
                                        }}
                                    >
                                        <Eye className="mr-2 h-4 w-4" />
                                        {t('admin.users.view', 'View')}
                                    </DropdownMenuItem>
                                    {!user.deleted_at && (
                                        <>
                                            <DropdownMenuItem
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    router.post(`/admin/users/${user.id}/impersonate`);
                                                }}
                                            >
                                                <User className="mr-2 h-4 w-4" />
                                                {t('admin.users.impersonate', 'Impersonate')}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    router.visit(`/admin/users/${user.id}/edit`);
                                                }}
                                            >
                                                <Edit className="mr-2 h-4 w-4" />
                                                {t('admin.users.edit', 'Edit')}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem className="text-destructive" onClick={(e) => handleDelete(user.id, e)}>
                                                <Trash2 className="mr-2 h-4 w-4" />
                                                {t('admin.users.delete', 'Delete')}
                                            </DropdownMenuItem>
                                        </>
                                    )}
                                    {user.deleted_at && (
                                        <>
                                            <DropdownMenuItem onClick={(e) => handleRestore(user.id, e)}>
                                                <RotateCcw className="mr-2 h-4 w-4" />
                                                {t('admin.users.restore', 'Restore')}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem className="text-destructive" onClick={(e) => handleForceDelete(user.id, e)}>
                                                <Trash className="mr-2 h-4 w-4" />
                                                {t('admin.users.force_delete', 'Force Delete')}
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
                    <Link href="/admin/users/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            {t('admin.users.create', 'Create User')}
                        </Button>
                    </Link>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div className="w-full sm:w-48">
                            <label className="mb-2 block text-sm font-medium">{t('admin.users.status', 'Status')}</label>
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
                    onRowClick={(user) => router.visit(`/admin/users/${user.id}`)}
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
