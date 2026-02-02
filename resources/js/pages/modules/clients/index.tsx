import { PaginatedDataTable } from '@/shared/components/paginated-data-table';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/shared/components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Edit, Eye, MoreVertical, Plus, RotateCcw, Trash, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { bulkDelete, bulkRestore, create, destroy, edit, forceDelete, index, restore, show } from '@/routes/admin/clients';

type Client = {
    id: string;
    name: string;
    contact_name?: string;
    contact_email?: string;
    status: string;
    created_at: string;
    deleted_at?: string;
};

type PageProps = SharedData & {
    clients: Client[];
    filters: {
        search?: string;
        status?: string;
        with_trashed?: boolean;
        only_trashed?: boolean;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    statuses: Record<string, string>;
};

export default function Index() {
    const { clients, filters, statuses } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const [status, setStatus] = useState(filters.status || '');
    const [withTrashed, setWithTrashed] = useState(filters.with_trashed || false);
    const [onlyTrashed, setOnlyTrashed] = useState(filters.only_trashed || false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.clients.navigation_label', 'Clients'),
            href: index().url,
        },
    ];

    // Apply client-side filters
    const filteredClients = useMemo(() => {
        let filtered = [...clients];

        if (status) {
            filtered = filtered.filter((client) => client.status === status);
        }

        if (withTrashed && onlyTrashed) {
            filtered = filtered.filter((client) => client.deleted_at);
        } else if (!withTrashed) {
            filtered = filtered.filter((client) => !client.deleted_at);
        }

        return filtered;
    }, [clients, status, withTrashed, onlyTrashed]);

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

    const handleDelete = (clientId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        if (confirm(t('admin.clients.confirm_delete', 'Are you sure you want to delete this client?'))) {
            router.delete(destroy({ client: clientId }).url, {
                preserveScroll: true,
            });
        }
    };

    const handleRestore = (clientId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        router.post(
            `/admin/clients/${clientId}/restore`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleForceDelete = (clientId: string, e: React.MouseEvent) => {
        e.stopPropagation();
        if (
            confirm(
                t(
                    'admin.clients.confirm_force_delete',
                    'Are you sure you want to permanently delete this client? This action cannot be undone.',
                ),
            )
        ) {
            router.delete(forceDelete({ client: clientId }).url, {
                preserveScroll: true,
            });
        }
    };

    const handleBulkDelete = (selectedClients: Client[]) => {
        const ids = selectedClients.map((c) => c.id);
        if (confirm(t('admin.clients.confirm_bulk_delete', `Are you sure you want to delete ${ids.length} clients?`))) {
            router.post(
                bulkDelete().url,
                { ids },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const handleBulkRestore = (selectedClients: Client[]) => {
        const ids = selectedClients.map((c) => c.id);
        router.post(
            bulkRestore().url,
            { ids },
            {
                preserveScroll: true,
            },
        );
    };

    const columns: ColumnDef<Client>[] = useMemo(
        () => [
            {
                id: 'select',
                size: 40,
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                        onChange={(e) => table.toggleAllPageRowsSelected(e.target.checked)}
                        aria-label="Select all"
                        data-row-select
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={(e) => row.toggleSelected(e.target.checked)}
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
                accessorKey: 'contact',
                header: t('admin.clients.table.contact_name'),
                cell: ({ row }) => (
                    <div>
                        {row.original.contact_name && <p className="font-medium">{row.original.contact_name}</p>}
                        {row.original.contact_email && <p className="text-sm text-muted-foreground">{row.original.contact_email}</p>}
                    </div>
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
                    const client = row.original;

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
                                            router.visit(show({ client: client.id }).url);
                                        }}
                                    >
                                        <Eye className="mr-2 h-4 w-4" />
                                        {t('common.actions.view')}
                                    </DropdownMenuItem>
                                    {!client.deleted_at && (
                                        <>
                                            <DropdownMenuItem
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    router.visit(edit({ client: client.id }).url);
                                                }}
                                            >
                                                <Edit className="mr-2 h-4 w-4" />
                                                {t('common.actions.edit')}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem className="text-destructive" onClick={(e) => handleDelete(client.id, e)}>
                                                <Trash2 className="mr-2 h-4 w-4" />
                                                {t('common.actions.delete')}
                                            </DropdownMenuItem>
                                        </>
                                    )}
                                    {client.deleted_at && (
                                        <>
                                            <DropdownMenuItem onClick={(e) => handleRestore(client.id, e)}>
                                                <RotateCcw className="mr-2 h-4 w-4" />
                                                {t('common.actions.restore')}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem className="text-destructive" onClick={(e) => handleForceDelete(client.id, e)}>
                                                <Trash className="mr-2 h-4 w-4" />
                                                {t('admin.clients.force_delete')}
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
            <Head title={t('admin.clients.navigation_label', 'Clients')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.clients.navigation_label', 'Clients')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.clients.description', 'Manage clients')}</p>
                    </div>
                    <Link href={create().url}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            {t('admin.clients.create', 'Create Client')}
                        </Button>
                    </Link>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div className="w-full sm:w-48">
                            <label className="mb-2 block text-sm font-medium">{t('common.labels.status')}</label>
                            <Select value={status || undefined} onValueChange={(value) => setStatus(value || '')}>
                                <SelectTrigger>
                                    <SelectValue placeholder={t('admin.clients.all_statuses', 'All Statuses')} />
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
                                {t('admin.clients.with_trashed', 'With Trashed')}
                            </label>
                            {withTrashed && (
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={onlyTrashed}
                                        onChange={(e) => setOnlyTrashed(e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    {t('admin.clients.only_trashed', 'Only Trashed')}
                                </label>
                            )}
                        </div>
                    </div>
                </div>

                <PaginatedDataTable
                    columns={columns}
                    data={filteredClients}
                    searchColumnIds={['name', 'contact_name', 'contact_email']}
                    searchPlaceholder={t('admin.clients.search_placeholder', 'Search by name, contact...')}
                    onRowClick={(client) => router.visit(show({ client: client.id }).url)}
                    enableRowSelection
                    bulkActionsRender={(selectedClients) => (
                        <>
                            {onlyTrashed ? (
                                <button
                                    type="button"
                                    className="w-full px-2 py-1.5 text-left text-sm hover:bg-muted"
                                    onClick={() => handleBulkRestore(selectedClients)}
                                >
                                    {t('admin.clients.bulk_restore', 'Restore Selected')}
                                </button>
                            ) : (
                                <button
                                    type="button"
                                    className="w-full px-2 py-1.5 text-left text-sm text-destructive hover:bg-destructive/10"
                                    onClick={() => handleBulkDelete(selectedClients)}
                                >
                                    {t('admin.clients.bulk_delete', 'Delete Selected')}
                                </button>
                            )}
                        </>
                    )}
                />
            </div>
        </AdminLayout>
    );
}
