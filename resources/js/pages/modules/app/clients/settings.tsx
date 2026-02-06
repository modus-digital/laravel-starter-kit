import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import AppLayout from '@/shared/layouts/app-layout';
import { type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
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
};

type PageProps = SharedData & {
    client: Client;
    errors?: Record<string, string>;
};

export default function Settings() {
    const { client, errors } = usePage<PageProps>().props;
    const { t } = useTranslation();

    return (
        <AppLayout>
            <Head title={t('clients.settings', 'Settings')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href={`/clients/${client.id}`}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-semibold">{t('clients.settings', 'Settings')}</h1>
                        <p className="text-sm text-muted-foreground">{t('clients.manage_settings', 'Manage your client profile')}</p>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-6">
                    <Form action={`/clients/${client.id}/settings`} method="put" className="space-y-6">
                        <div className="space-y-4">
                            <h2 className="text-lg font-medium">{t('admin.clients.form.base.title', 'Client Information')}</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="name">{t('common.labels.name')} *</Label>
                                    <Input id="name" name="name" defaultValue={client.name} required />
                                    <InputError message={errors?.name} />
                                </div>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <h2 className="text-lg font-medium">{t('admin.clients.form.contact_information.title', 'Contact Information')}</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="contact_name">{t('admin.clients.form.contact_information.contact_name', 'Contact Name')}</Label>
                                    <Input id="contact_name" name="contact_name" defaultValue={client.contact_name || ''} />
                                    <InputError message={errors?.contact_name} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="contact_email">
                                        {t('admin.clients.form.contact_information.contact_email', 'Contact Email')}
                                    </Label>
                                    <Input id="contact_email" name="contact_email" type="email" defaultValue={client.contact_email || ''} />
                                    <InputError message={errors?.contact_email} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="contact_phone">
                                        {t('admin.clients.form.contact_information.contact_phone', 'Contact Phone')}
                                    </Label>
                                    <Input id="contact_phone" name="contact_phone" defaultValue={client.contact_phone || ''} />
                                    <InputError message={errors?.contact_phone} />
                                </div>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <h2 className="text-lg font-medium">{t('admin.clients.form.location.title', 'Location')}</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="address">{t('common.labels.address')}</Label>
                                    <Input id="address" name="address" defaultValue={client.address || ''} />
                                    <InputError message={errors?.address} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="city">{t('common.labels.city')}</Label>
                                    <Input id="city" name="city" defaultValue={client.city || ''} />
                                    <InputError message={errors?.city} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="postal_code">{t('common.labels.postal_code')}</Label>
                                    <Input id="postal_code" name="postal_code" defaultValue={client.postal_code || ''} />
                                    <InputError message={errors?.postal_code} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="country">{t('common.labels.country')}</Label>
                                    <Input id="country" name="country" defaultValue={client.country || ''} />
                                    <InputError message={errors?.country} />
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit">{t('common.actions.save')}</Button>
                        </div>
                    </Form>
                </div>
            </div>
        </AppLayout>
    );
}
