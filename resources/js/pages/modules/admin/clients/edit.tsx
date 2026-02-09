import { edit, index, show, update } from '@/routes/admin/clients';
import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Client } from '@/types/admin/clients';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type PageProps = SharedData & {
    client: Client;
    statuses: Record<string, string>;
};

export default function Edit() {
    const { client, statuses } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.clients.navigation_label', 'Clients'),
            href: index().url,
        },
        {
            title: client.name,
            href: show({ client: client.id }).url,
        },
        {
            title: t('admin.clients.edit', 'Edit'),
            href: edit({ client: client.id }).url,
        },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('admin.clients.edit', 'Edit Client')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href={`/admin/clients/${client.id}`}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.clients.edit', 'Edit Client')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.clients.edit_description', 'Update client information')}</p>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-6">
                    <Form {...update.form({ client: client.id })} className="space-y-6">
                        {({ processing, errors }) => (
                            <>
                                <div className="space-y-6">
                                    <div>
                                        <h3 className="mb-4 text-lg font-medium">{t('admin.clients.form.base.title', 'Basic Information')}</h3>
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="name">{t('common.labels.name')}</Label>
                                                <Input id="name" name="name" defaultValue={client.name} />
                                                <InputError message={errors.name} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="status">{t('common.labels.status')}</Label>
                                                <Select name="status" defaultValue={client.status}>
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('admin.clients.select_status')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {Object.entries(statuses).map(([value, label]) => (
                                                            <SelectItem key={value} value={value}>
                                                                {label}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.status} />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 className="mb-4 text-lg font-medium">
                                            {t('admin.clients.form.contact_information.title', 'Contact Information')}
                                        </h3>
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="space-y-2 md:col-span-2">
                                                <Label htmlFor="contact_name">
                                                    {t('admin.clients.form.contact_information.contact_name', 'Contact Name')}
                                                </Label>
                                                <Input id="contact_name" name="contact_name" defaultValue={client.contact_name || ''} />
                                                <InputError message={errors.contact_name} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="contact_email">
                                                    {t('admin.clients.form.contact_information.contact_email', 'Contact Email')}
                                                </Label>
                                                <Input
                                                    id="contact_email"
                                                    name="contact_email"
                                                    type="email"
                                                    defaultValue={client.contact_email || ''}
                                                />
                                                <InputError message={errors.contact_email} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="contact_phone">
                                                    {t('admin.clients.form.contact_information.contact_phone', 'Contact Phone')}
                                                </Label>
                                                <Input id="contact_phone" name="contact_phone" type="tel" defaultValue={client.contact_phone || ''} />
                                                <InputError message={errors.contact_phone} />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 className="mb-4 text-lg font-medium">{t('admin.clients.form.location.title')}</h3>
                                        <div className="grid gap-6 md:grid-cols-4">
                                            <div className="space-y-2 md:col-span-3">
                                                <Label htmlFor="address">{t('common.labels.address')}</Label>
                                                <Input id="address" name="address" defaultValue={client.address || ''} />
                                                <InputError message={errors.address} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="postal_code">{t('common.labels.postal_code')}</Label>
                                                <Input id="postal_code" name="postal_code" defaultValue={client.postal_code || ''} />
                                                <InputError message={errors.postal_code} />
                                            </div>

                                            <div className="space-y-2 md:col-span-2">
                                                <Label htmlFor="city">{t('common.labels.city')}</Label>
                                                <Input id="city" name="city" defaultValue={client.city || ''} />
                                                <InputError message={errors.city} />
                                            </div>

                                            <div className="space-y-2 md:col-span-2">
                                                <Label htmlFor="country">{t('common.labels.country')}</Label>
                                                <Input id="country" name="country" defaultValue={client.country || ''} />
                                                <InputError message={errors.country} />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('common.status.saving') : t('common.actions.save')}
                                    </Button>
                                    <Link href={show({ client: client.id }).url}>
                                        <Button type="button" variant="outline">
                                            {t('common.actions.cancel')}
                                        </Button>
                                    </Link>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </AdminLayout>
    );
}
