import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable } from '@/components/data-table';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { useMemo, useState } from 'react';
import { ArrowUpDown, CheckCircle, Clock, EllipsisVertical, Eye, Mail, Trash2 } from 'lucide-react';

type NotificationItem = {
    id: string;
    title: string;
    body?: string | null;
    action_url?: string | null;
    read_at?: string | null;
    created_at: string;
};

type PaginationLinks = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLinks[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type NotificationsProps = {
    notifications: Paginated<NotificationItem>;
    unreadCount: number;
    activeTab: 'all' | 'read';
};

export default function Notifications({ notifications, unreadCount, activeTab }: NotificationsProps) {
    const [selectedNotifications, setSelectedNotifications] = useState<NotificationItem[]>([]);

    const openDetails = (id: string) => {
        router.visit(`/notifications/${id}`);
    };

    const markRead = (id: string) =>
        router.post(`/notifications/${id}/read`, undefined, { preserveScroll: true, preserveState: true });

    const markUnread = (id: string) =>
        router.post(`/notifications/${id}/unread`, undefined, { preserveScroll: true, preserveState: true });

    const remove = (id: string) =>
        router.delete(`/notifications/${id}`, { preserveScroll: true, preserveState: true });

    const clearAll = () => router.delete('/notifications', { preserveScroll: true, preserveState: true });

    const columns: ColumnDef<NotificationItem>[] = useMemo(
        () => [
            {
                id: 'select',
                header: ({ table }) => (
                    <Checkbox
                        checked={
                            table.getIsAllPageRowsSelected() ||
                            (table.getIsSomePageRowsSelected() && 'indeterminate')
                        }
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
                id: 'status',
                accessorFn: (row) => (row.read_at ? 'Read' : 'Unread'),
                header: ({ column }) => (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 px-2 text-xs font-medium"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    >
                        <span>Status</span>
                        <ArrowUpDown className="ml-1 size-3.5" />
                    </Button>
                ),
                cell: ({ row }) => {
                    const read = row.original.read_at !== null && row.original.read_at !== undefined;

                    return (
                        <Badge variant={read ? 'outline' : 'default'}>
                            {read ? 'Read' : 'Unread'}
                        </Badge>
                    );
                },
                enableHiding: false,
            },
            {
                accessorKey: 'title',
                header: ({ column }) => (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 px-2 text-xs font-medium"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    >
                        <span>Title</span>
                        <ArrowUpDown className="ml-1 size-3.5" />
                    </Button>
                ),
                cell: ({ row }) => (
                    <div className="flex items-center gap-2">
                        <span className="truncate text-sm font-medium">
                            {row.original.title || 'Notification'}
                        </span>
                        {row.original.action_url && (
                            <a
                                href={row.original.action_url}
                                className="hidden text-xs text-primary hover:underline sm:inline"
                                target="_blank"
                                rel="noreferrer"
                                data-row-action
                                onClick={(event) => event.stopPropagation()}
                            >
                                Open link
                            </a>
                        )}
                    </div>
                ),
            },
            {
                accessorKey: 'created_at',
                header: ({ column }) => (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="ml-auto flex h-8 items-center justify-end px-2 text-xs font-medium"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    >
                        <span className="mr-1">Date</span>
                        <ArrowUpDown className="size-3.5" />
                    </Button>
                ),
                cell: ({ row }) => (
                    <div className="flex items-center justify-end gap-1.5 text-xs text-muted-foreground">
                        <Clock className="size-4" />
                        <time dateTime={row.original.created_at}>
                            {new Date(row.original.created_at).toLocaleString()}
                        </time>
                    </div>
                ),
            },
            {
                id: 'actions',
                enableSorting: false,
                enableHiding: false,
                cell: ({ row }) => {
                    const notification = row.original;

                    return (
                        <div className="flex items-center justify-end" data-row-action>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="h-8 w-8 shrink-0 text-muted-foreground hover:text-foreground"
                                        onClick={(event) => event.stopPropagation()}
                                    >
                                        <EllipsisVertical className="size-4" />
                                        <span className="sr-only">Open notification actions</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    className="w-44"
                                    onClick={(event) => event.stopPropagation()}
                                >
                                    <DropdownMenuItem
                                        onClick={(event) => {
                                            event.stopPropagation();
                                            openDetails(notification.id);
                                        }}
                                    >
                                        <Eye className="size-4" />
                                        <span>View details</span>
                                    </DropdownMenuItem>
                                    {!notification.read_at ? (
                                        <DropdownMenuItem
                                            onClick={(event) => {
                                                event.stopPropagation();
                                                markRead(notification.id);
                                            }}
                                        >
                                            <CheckCircle className="size-4" />
                                            <span>Mark as read</span>
                                        </DropdownMenuItem>
                                    ) : (
                                        <DropdownMenuItem
                                            onClick={(event) => {
                                                event.stopPropagation();
                                                markUnread(notification.id);
                                            }}
                                        >
                                            <Mail className="size-4" />
                                            <span>Mark as unread</span>
                                        </DropdownMenuItem>
                                    )}
                                    <DropdownMenuItem
                                        variant="destructive"
                                        onClick={(event) => {
                                            event.stopPropagation();
                                            remove(notification.id);
                                        }}
                                    >
                                        <Trash2 className="size-4" />
                                        <span>Delete</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    );
                },
            },
        ],
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [markRead, markUnread, openDetails, remove],
    );

    const handleBulkMarkRead = () => {
        const ids = selectedNotifications.filter((notification) => !notification.read_at).map((n) => n.id);

        if (ids.length === 0) {
            return;
        }

        router.post(
            '/notifications/bulk/read',
            { ids },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const handleBulkMarkUnread = () => {
        const ids = selectedNotifications.filter((notification) => notification.read_at).map((n) => n.id);

        if (ids.length === 0) {
            return;
        }

        router.post(
            '/notifications/bulk/unread',
            { ids },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const handleBulkClear = () => {
        clearAll();
    };

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: 'Notifications',
                    href: '/notifications',
                },
            ]}
        >
            <Head title="Notifications" />
            <div className="mx-auto my-8 flex w-full max-w-7xl flex-col gap-4">
                <div className="flex w-full items-center justify-between">
                    <div className="flex items-center gap-2">
                        <h1 className="text-xl font-semibold">Notifications</h1>
                        <Badge variant={unreadCount > 0 ? 'default' : 'outline'}>
                            {unreadCount} unread
                        </Badge>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={notifications.data.length === 0}
                            onClick={clearAll}
                        >
                            <Trash2 className="size-4" />
                            Clear all
                        </Button>
                    </div>
                </div>

                <Card className="w-full">
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <CardTitle>Inbox</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Stay on top of the latest activity from across your projects.
                            </p>
                        </div>
                        <div className="inline-flex items-center rounded-full bg-muted p-1 text-xs">
                            {(['all', 'read'] as const).map((tab) => (
                                <button
                                    key={tab}
                                    type="button"
                                    onClick={() =>
                                        router.visit(
                                            tab === 'all' ? '/notifications' : `/notifications?tab=${tab}`,
                                            {
                                                preserveScroll: true,
                                                preserveState: true,
                                            },
                                        )
                                    }
                                    className={`flex items-center rounded-full px-3 py-1.5 text-xs font-medium capitalize transition-colors ${
                                        activeTab === tab
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    {tab === 'all' ? (
                                        <span className="flex items-center">
                                            All
                                            {unreadCount > 0 && (
                                                <span className="ml-2 inline-flex min-w-[1.5em] items-center justify-center rounded-full bg-primary text-primary-foreground text-[10px] font-bold px-1.5 py-0.5">
                                                    {unreadCount}
                                                </span>
                                            )}
                                        </span>
                                    ) : (
                                        'Read'
                                    )}
                                </button>
                            ))}
                        </div>
                    </CardHeader>
                    <Separator />
                    <CardContent className="p-4">
                        <DataTable
                            columns={columns}
                            data={notifications.data}
                            searchColumnIds={['title', 'created_at']}
                            searchPlaceholder="Search by title or date…"
                            enableRowSelection
                            onSelectionChange={setSelectedNotifications}
                            bulkActionsRender={() => (
                                <>
                                    <button
                                        type="button"
                                        className="w-full px-2 py-1.5 text-left text-sm hover:bg-muted"
                                        onClick={handleBulkMarkRead}
                                    >
                                        Mark all as read
                                    </button>
                                    <button
                                        type="button"
                                        className="w-full px-2 py-1.5 text-left text-sm hover:bg-muted"
                                        onClick={handleBulkMarkUnread}
                                    >
                                        Mark all as unread
                                    </button>
                                    <button
                                        type="button"
                                        className="w-full px-2 py-1.5 text-left text-sm text-destructive hover:bg-destructive/10"
                                        onClick={handleBulkClear}
                                    >
                                        Clear all
                                    </button>
                                </>
                            )}
                            onRowClick={(notification) => openDetails(notification.id)}
                            pagination={{
                                currentPage: notifications.current_page,
                                lastPage: notifications.last_page,
                                total: notifications.total,
                                onPageChange: (page) =>
                                    router.visit(
                                        activeTab === 'all'
                                            ? `/notifications?page=${page}`
                                            : `/notifications?tab=${activeTab}&page=${page}`,
                                        {
                                            preserveScroll: true,
                                            preserveState: true,
                                        },
                                    ),
                            }}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}