import AdminLayout from '@/shared/layouts/admin/layout';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Checkbox } from '@/shared/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Separator } from '@/shared/components/ui/separator';
import { Alert, AlertDescription } from '@/shared/components/ui/alert';
import { type SharedData } from '@/types';
import { Head, useForm, router } from '@inertiajs/react';
import { ArrowLeft, AlertTriangle } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import InputError from '@/shared/components/input-error';

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
    icon?: string;
    color?: string;
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
        icon: role.icon || '',
        color: role.color || '',
        permissions: role.permissions,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/roles/${role.id}`);
    };

    const togglePermission = (permissionName: string) => {
        if (data.permissions.includes(permissionName)) {
            setData('permissions', data.permissions.filter(p => p !== permissionName));
        } else {
            setData('permissions', [...data.permissions, permissionName]);
        }
    };

    const toggleCategory = (category: string) => {
        const categoryPermissions = permissions[category].map(p => p.name);
        const allSelected = categoryPermissions.every(p => data.permissions.includes(p));

        if (allSelected) {
            setData('permissions', data.permissions.filter(p => !categoryPermissions.includes(p)));
        } else {
            const newPermissions = [...data.permissions];
            categoryPermissions.forEach(p => {
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
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => router.visit(`/admin/roles/${role.id}`)}
                    >
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
                        <AlertDescription>
                            {t('admin.roles.system_role_warning')}
                        </AlertDescription>
                    </Alert>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
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
                                <p className="text-xs text-muted-foreground">
                                    {t('admin.roles.form.name_helper')}
                                </p>
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="icon">{t('admin.roles.form.icon')}</Label>
                                    <Input
                                        id="icon"
                                        value={data.icon}
                                        onChange={(e) => setData('icon', e.target.value)}
                                        placeholder="heroicon-o-user"
                                    />
                                    <InputError message={errors.icon} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="color">{t('admin.roles.form.color')}</Label>
                                    <Input
                                        id="color"
                                        type="color"
                                        value={data.color || '#000000'}
                                        onChange={(e) => setData('color', e.target.value)}
                                    />
                                    <InputError message={errors.color} />
                                </div>
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
                                            checked={categoryPermissions.every(p => data.permissions.includes(p.name))}
                                            onCheckedChange={() => toggleCategory(category)}
                                            disabled={isSystemRole}
                                        />
                                        <label
                                            htmlFor={`category-${category}`}
                                            className="text-sm font-semibold leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
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
                                                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                                    >
                                                        {permission.label}
                                                    </label>
                                                    {permission.description && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {permission.description}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    {category !== Object.keys(permissions)[Object.keys(permissions).length - 1] && (
                                        <Separator className="my-4" />
                                    )}
                                </div>
                            ))}
                            <InputError message={errors.permissions} />
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => router.visit(`/admin/roles/${role.id}`)}
                        >
                            {t('common.actions.cancel')}
                        </Button>
                        <Button type="submit" disabled={processing || isSystemRole}>
                            {processing ? t('common.saving') : t('common.actions.save')}
                        </Button>
                    </div>
                </form>
            </div>
            </div>
        </AdminLayout>
    );
}
