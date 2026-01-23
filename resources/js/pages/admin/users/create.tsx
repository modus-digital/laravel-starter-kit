import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type PageProps = SharedData & {
    roles: Array<{ name: string; label: string }>;
    statuses: Record<string, string>;
};

export default function Create() {
    const { roles, statuses } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('admin.users.navigation_label', 'Users'),
            href: '/admin/users',
        },
        {
            title: t('admin.users.create', 'Create User'),
            href: '/admin/users/create',
        },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('admin.users.create', 'Create User')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href="/admin/users">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.users.create', 'Create User')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.users.create_description', 'Add a new user to the system')}</p>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-6">
                    <Form
                        action="/admin/users"
                        method="post"
                        className="space-y-6"
                        onSubmit={(e) => {
                            const form = e.currentTarget;
                            const roleSelect = form.querySelector<HTMLSelectElement>('select[name="role"]');
                            if (roleSelect?.value) {
                                const rolesInput = document.createElement('input');
                                rolesInput.type = 'hidden';
                                rolesInput.name = 'roles[]';
                                rolesInput.value = roleSelect.value;
                                form.appendChild(rolesInput);
                            }
                        }}
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">{t('admin.users.form.name', 'Name')} *</Label>
                                        <Input id="name" name="name" required />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">{t('admin.users.form.email', 'Email')} *</Label>
                                        <Input id="email" name="email" type="email" required />
                                        <InputError message={errors.email} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="phone">{t('admin.users.form.phone', 'Phone')}</Label>
                                        <Input id="phone" name="phone" type="tel" />
                                        <InputError message={errors.phone} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="password">{t('admin.users.form.password', 'Password')} *</Label>
                                        <Input id="password" name="password" type="password" required />
                                        <InputError message={errors.password} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="role">{t('admin.users.form.role', 'Role')} *</Label>
                                        <Select name="role" required>
                                            <SelectTrigger>
                                                <SelectValue placeholder={t('admin.users.select_role', 'Select a role')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roles.map((role) => (
                                                    <SelectItem key={role.name} value={role.name}>
                                                        {role.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.role || errors.roles} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="status">{t('admin.users.form.status', 'Status')} *</Label>
                                        <Select name="status" required>
                                            <SelectTrigger>
                                                <SelectValue placeholder={t('admin.users.select_status', 'Select a status')} />
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

                                <div className="flex items-center gap-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('admin.users.creating', 'Creating...') : t('admin.users.create', 'Create User')}
                                    </Button>
                                    <Link href="/admin/users">
                                        <Button type="button" variant="outline">
                                            {t('admin.users.cancel', 'Cancel')}
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
