import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type PageProps = SharedData & {
    statuses: Record<string, string>;
};

export default function Create() {
    const { statuses } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.clients.navigation_label', 'Clients'),
            href: '/admin/clients',
        },
        {
            title: t('admin.clients.create', 'Create Client'),
            href: '/admin/clients/create',
        },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('admin.clients.create', 'Create Client')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href="/admin/clients">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.clients.create', 'Create Client')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.clients.create_description', 'Add a new client')}</p>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-6">
                    <Form
                        action="/admin/clients"
                        method="post"
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="space-y-6">
                                    <div>
                                        <h3 className="mb-4 text-lg font-medium">{t('admin.clients.form.base.title', 'Basic Information')}</h3>
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="name">{t('admin.clients.form.base.name', 'Name')} *</Label>
                                                <Input id="name" name="name" required />
                                                <InputError message={errors.name} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="status">{t('admin.clients.form.base.status', 'Status')} *</Label>
                                                <Select name="status" required>
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('admin.clients.select_status', 'Select a status')} />
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
                                        <h3 className="mb-4 text-lg font-medium">{t('admin.clients.form.contact_information.title', 'Contact Information')}</h3>
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="space-y-2 md:col-span-2">
                                                <Label htmlFor="contact_name">{t('admin.clients.form.contact_information.contact_name', 'Contact Name')}</Label>
                                                <Input id="contact_name" name="contact_name" />
                                                <InputError message={errors.contact_name} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="contact_email">{t('admin.clients.form.contact_information.contact_email', 'Contact Email')}</Label>
                                                <Input id="contact_email" name="contact_email" type="email" />
                                                <InputError message={errors.contact_email} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="contact_phone">{t('admin.clients.form.contact_information.contact_phone', 'Contact Phone')}</Label>
                                                <Input id="contact_phone" name="contact_phone" type="tel" />
                                                <InputError message={errors.contact_phone} />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 className="mb-4 text-lg font-medium">{t('admin.clients.form.location.title', 'Location')}</h3>
                                        <div className="grid gap-6 md:grid-cols-4">
                                            <div className="space-y-2 md:col-span-3">
                                                <Label htmlFor="address">{t('admin.clients.form.location.address', 'Address')}</Label>
                                                <Input id="address" name="address" />
                                                <InputError message={errors.address} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="postal_code">{t('admin.clients.form.location.postal_code', 'Postal Code')}</Label>
                                                <Input id="postal_code" name="postal_code" />
                                                <InputError message={errors.postal_code} />
                                            </div>

                                            <div className="space-y-2 md:col-span-2">
                                                <Label htmlFor="city">{t('admin.clients.form.location.city', 'City')}</Label>
                                                <Input id="city" name="city" />
                                                <InputError message={errors.city} />
                                            </div>

                                            <div className="space-y-2 md:col-span-2">
                                                <Label htmlFor="country">{t('admin.clients.form.location.country', 'Country')}</Label>
                                                <Input id="country" name="country" />
                                                <InputError message={errors.country} />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('admin.clients.creating', 'Creating...') : t('admin.clients.create', 'Create Client')}
                                    </Button>
                                    <Link href="/admin/clients">
                                        <Button type="button" variant="outline">
                                            {t('admin.clients.cancel', 'Cancel')}
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
