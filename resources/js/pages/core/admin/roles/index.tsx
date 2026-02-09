import { destroy, edit, show } from '@/routes/admin/roles';
import { PaginatedDataTable } from '@/shared/components/paginated-data-table';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/shared/components/ui/dropdown-menu';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type SharedData } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Edit, Eye, MoreVertical, Plus, Trash2 } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

type Role = {
    id: string;
    name: string;
    guard_name: string;
    permissions_count: number;
    users_count: number;
    created_at: string;
    updated_at: string;
};

type PageProps = SharedData & {
    roles: Role[];
    filters: {
        search?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
};

export default function Index({ roles }: PageProps) {
    const { t } = useTranslation();

    const columns: ColumnDef<Role>[] = useMemo(
        () => [
            {
                accessorKey: 'name',
                header: t('common.labels.name'),
                cell: ({ row }) => <Badge variant="outline">{t(`enums.rbac.role.${row.original.name}` as never)}</Badge>,
            },
            {
                accessorKey: 'guard_name',
                header: t('admin.roles.form.guard_name'),
                cell: ({ row }) => row.original.guard_name,
            },
            {
                accessorKey: 'permissions_count',
                header: t('admin.roles.table.permissions_count'),
                cell: ({ row }) => row.original.permissions_count,
            },
            {
                accessorKey: 'users_count',
                header: t('admin.roles.table.users_count'),
                cell: ({ row }) => row.original.users_count,
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
                    const role = row.original;

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
                                            router.visit(show({ role: role.id }).url);
                                        }}
                                    >
                                        <Eye className="mr-2 h-4 w-4" />
                                        {t('common.actions.view')}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            router.visit(edit({ role: role.id }).url);
                                        }}
                                    >
                                        <Edit className="mr-2 h-4 w-4" />
                                        {t('common.actions.edit')}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        className="text-destructive"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            if (confirm(t('admin.roles.confirm_delete'))) {
                                                router.delete(destroy({ role: role.id }).url, {
                                                    preserveScroll: true,
                                                });
                                            }
                                        }}
                                    >
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        {t('common.actions.delete')}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    );
                },
            },
        ],
        [t],
    );

    return (
        <AdminLayout>
            <Head title={t('admin.roles.title')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold">{t('admin.roles.title')}</h1>
                            <p className="text-sm text-muted-foreground">{t('admin.roles.description')}</p>
                        </div>
                        <Link href="/admin/roles/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('admin.roles.create')}
                            </Button>
                        </Link>
                    </div>

                    <PaginatedDataTable
                        columns={columns}
                        data={roles}
                        searchColumnIds={['name']}
                        searchPlaceholder={t('admin.roles.search_placeholder')}
                        onRowClick={(role) => router.visit(show({ role: role.id }).url)}
                        enableRowSelection={false}
                    />
                </div>
            </div>
        </AdminLayout>
    );
}
