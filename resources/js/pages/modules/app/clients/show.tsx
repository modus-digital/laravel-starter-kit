import { Badge } from '@/shared/components/ui/badge';
import AppLayout from '@/shared/layouts/app-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, FileText, Settings, Users } from 'lucide-react';
import { useTranslation } from 'react-i18next';

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
};

type PageProps = SharedData & {
    client: Client;
};

export default function Show() {
    const { client } = usePage<PageProps>().props;
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
            <Head title={client.name} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{client.name}</h1>
                        <p className="text-sm text-muted-foreground">{t('clients.dashboard', 'Dashboard')}</p>
                    </div>
                    <Badge variant={getStatusColor(client.status)}>{client.status}</Badge>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <Link href="/manage/users">
                        <div className="flex h-full cursor-pointer items-center gap-4 rounded-lg border bg-card p-6 transition-colors hover:bg-muted">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <Users className="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-medium">{t('sidebar.manage_users', 'Manage Users')}</h3>
                                <p className="text-sm text-muted-foreground">
                                    {t('clients.manage_users_description', 'View and manage client users')}
                                </p>
                            </div>
                        </div>
                    </Link>

                    <Link href="/manage/settings">
                        <div className="flex h-full cursor-pointer items-center gap-4 rounded-lg border bg-card p-6 transition-colors hover:bg-muted">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <Settings className="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-medium">{t('clients.settings', 'Settings')}</h3>
                                <p className="text-sm text-muted-foreground">{t('clients.manage_settings', 'Manage your profile')}</p>
                            </div>
                        </div>
                    </Link>

                    <Link href="/activities">
                        <div className="flex h-full cursor-pointer items-center gap-4 rounded-lg border bg-card p-6 transition-colors hover:bg-muted">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <FileText className="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-medium">{t('admin.clients.activities', 'Activities')}</h3>
                                <p className="text-sm text-muted-foreground">{t('clients.view_activities', 'View activity log')}</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="rounded-lg border bg-card p-6">
                        <div className="mb-4 flex items-center gap-2">
                            <Building2 className="h-5 w-5 text-muted-foreground" />
                            <h2 className="text-lg font-semibold">{t('admin.clients.details', 'Details')}</h2>
                        </div>
                        <dl className="space-y-4">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.name')}</dt>
                                <dd className="mt-1 text-sm">{client.name}</dd>
                            </div>
                            {client.contact_name && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.contact_name', 'Contact Name')}</dt>
                                    <dd className="mt-1 text-sm">{client.contact_name}</dd>
                                </div>
                            )}
                            {client.contact_email && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.email')}</dt>
                                    <dd className="mt-1 text-sm">{client.contact_email}</dd>
                                </div>
                            )}
                            {client.contact_phone && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.phone')}</dt>
                                    <dd className="mt-1 text-sm">{client.contact_phone}</dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    {(client.address || client.city || client.country) && (
                        <div className="rounded-lg border bg-card p-6">
                            <h2 className="mb-4 text-lg font-semibold">{t('common.labels.address', 'Address')}</h2>
                            <dl className="space-y-4">
                                {client.address && (
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.address')}</dt>
                                        <dd className="mt-1 text-sm">{client.address}</dd>
                                    </div>
                                )}
                                <div className="grid grid-cols-2 gap-4">
                                    {client.city && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.city')}</dt>
                                            <dd className="mt-1 text-sm">{client.city}</dd>
                                        </div>
                                    )}
                                    {client.postal_code && (
                                        <div>
                                            <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.postal_code')}</dt>
                                            <dd className="mt-1 text-sm">{client.postal_code}</dd>
                                        </div>
                                    )}
                                </div>
                                {client.country && (
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">{t('common.labels.country')}</dt>
                                        <dd className="mt-1 text-sm">{client.country}</dd>
                                    </div>
                                )}
                            </dl>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
