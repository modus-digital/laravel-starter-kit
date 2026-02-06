import { Badge } from '@/shared/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import AppLayout from '@/shared/layouts/app-layout';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Users as UsersIcon } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type Client = {
    id: string;
    name: string;
};

type User = {
    id: string;
    name: string;
    email: string;
    status: string;
    role?: string;
};

type PageProps = SharedData & {
    client: Client;
    users: User[];
};

export default function Users() {
    const { client, users } = usePage<PageProps>().props;
    const { t } = useTranslation();

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
        <AppLayout>
            <Head title={t('sidebar.manage_users', 'Manage Users')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('sidebar.manage_users', 'Manage Users')}</h1>
                        <p className="text-sm text-muted-foreground">{client.name}</p>
                    </div>
                </div>

                <div className="rounded-lg border bg-card p-6">
                    <div className="mb-4 flex items-center gap-2">
                        <UsersIcon className="h-5 w-5 text-muted-foreground" />
                        <h2 className="text-lg font-semibold">{t('common.labels.users', 'Users')}</h2>
                    </div>
                    {users.length === 0 ? (
                        <p className="text-center text-sm text-muted-foreground">{t('admin.clients.no_users', 'No users found.')}</p>
                    ) : (
                        <div className="overflow-hidden rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('common.labels.name')}</TableHead>
                                        <TableHead>{t('common.labels.email')}</TableHead>
                                        <TableHead>{t('common.labels.role', 'Role')}</TableHead>
                                        <TableHead>{t('common.labels.status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {users.map((user) => (
                                        <TableRow key={user.id}>
                                            <TableCell className="font-medium">{user.name}</TableCell>
                                            <TableCell>{user.email}</TableCell>
                                            <TableCell>
                                                {user.role ? user.role.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) : '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusColor(user.status)}>{user.status}</Badge>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
