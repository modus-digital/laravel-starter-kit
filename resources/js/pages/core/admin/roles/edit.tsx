import { show, update } from '@/routes/admin/roles';
import InputError from '@/shared/components/input-error';
import { Alert, AlertDescription } from '@/shared/components/ui/alert';
import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Checkbox } from '@/shared/components/ui/checkbox';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Separator } from '@/shared/components/ui/separator';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type SharedData } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type Permission = {
    id: string;
    name: string;
    label: string;
    description: string;
    category: string;
};

type Role = {
    id: string;
    name: string;
    guard_name: string;
    permissions: string[];
};

type PageProps = SharedData & {
    role: Role;
    permissions: Record<string, Permission[]>;
    isSystemRole: boolean;
};

export default function Edit({ role, permissions, isSystemRole }: PageProps) {
    const { t } = useTranslation();

    const { data, setData, put, processing, errors } = useForm({
        name: role.name,
        guard_name: role.guard_name,
        permissions: role.permissions,
    });

    const togglePermission = (permissionName: string) => {
        if (data.permissions.includes(permissionName)) {
            setData(
                'permissions',
                data.permissions.filter((p) => p !== permissionName),
            );
        } else {
            setData('permissions', [...data.permissions, permissionName]);
        }
    };

    const toggleCategory = (category: string) => {
        const categoryPermissions = permissions[category].map((p) => p.name);
        const allSelected = categoryPermissions.every((p) => data.permissions.includes(p));

        if (allSelected) {
            setData(
                'permissions',
                data.permissions.filter((p) => !categoryPermissions.includes(p)),
            );
        } else {
            const newPermissions = [...data.permissions];
            categoryPermissions.forEach((p) => {
                if (!newPermissions.includes(p)) {
                    newPermissions.push(p);
                }
            });
            setData('permissions', newPermissions);
        }
    };

    return (
        <AdminLayout>
            <Head title={t('admin.roles.edit')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" onClick={() => router.visit(show({ role: role.id }).url)}>
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-2xl font-semibold">{t('admin.roles.edit')}</h1>
                            <p className="text-sm text-muted-foreground">{t('admin.roles.edit_description')}</p>
                        </div>
                    </div>

                    {isSystemRole && (
                        <Alert variant="destructive">
                            <AlertTriangle className="h-4 w-4" />
                            <AlertDescription>{t('admin.roles.system_role_warning')}</AlertDescription>
                        </Alert>
                    )}

                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            put(update({ role: role.id }).url, {
                                preserveScroll: true,
                            });
                        }}
                        className="space-y-6"
                    >
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.roles.form.basic_information')}</CardTitle>
                                <CardDescription>{t('admin.roles.form.basic_information_description')}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">{t('admin.roles.form.name')}</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g. manager"
                                        disabled={isSystemRole}
                                    />
                                    <p className="text-xs text-muted-foreground">{t('admin.roles.form.name_helper')}</p>
                                    <InputError message={errors.name} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.roles.form.permissions')}</CardTitle>
                                <CardDescription>{t('admin.roles.form.permissions_description')}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {Object.entries(permissions).map(([category, categoryPermissions]) => (
                                    <div key={category} className="space-y-3">
                                        <div className="flex items-center space-x-2">
                                            <Checkbox
                                                id={`category-${category}`}
                                                checked={categoryPermissions.every((p) => data.permissions.includes(p.name))}
                                                onCheckedChange={() => toggleCategory(category)}
                                                disabled={isSystemRole}
                                            />
                                            <label
                                                htmlFor={`category-${category}`}
                                                className="text-sm leading-none font-semibold peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                            >
                                                {category}
                                            </label>
                                        </div>
                                        <div className="ml-6 space-y-2">
                                            {categoryPermissions.map((permission) => (
                                                <div key={permission.id} className="flex items-start space-x-2">
                                                    <Checkbox
                                                        id={permission.id}
                                                        checked={data.permissions.includes(permission.name)}
                                                        onCheckedChange={() => togglePermission(permission.name)}
                                                        disabled={isSystemRole}
                                                    />
                                                    <div className="grid gap-1.5 leading-none">
                                                        <label
                                                            htmlFor={permission.id}
                                                            className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                                        >
                                                            {permission.label}
                                                        </label>
                                                        {permission.description && (
                                                            <p className="text-xs text-muted-foreground">{permission.description}</p>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                        {category !== Object.keys(permissions)[Object.keys(permissions).length - 1] && <Separator className="my-4" />}
                                    </div>
                                ))}
                                <InputError message={errors.permissions} />
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-4">
                            <Button type="button" variant="outline" onClick={() => router.visit(show({ role: role.id }).url)}>
                                {t('common.actions.cancel')}
                            </Button>
                            <Button type="submit" disabled={processing || isSystemRole}>
                                {processing ? t('common.status.saving') : t('common.actions.save')}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
