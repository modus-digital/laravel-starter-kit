import AdminLayout from '@/shared/layouts/admin/layout';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Checkbox } from '@/shared/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Separator } from '@/shared/components/ui/separator';
import { type SharedData } from '@/types';
import { Form, Head, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import InputError from '@/shared/components/input-error';
import { index, store } from '@/routes/admin/roles';

type Permission = {
    id: string;
    name: string;
    label: string;
    description: string;
    category: string;
};

type PageProps = SharedData & {
    permissions: Record<string, Permission[]>;
};

export default function Create({ permissions }: PageProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        guard_name: 'web',
        icon: '',
        color: '',
        permissions: [] as string[],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store().url);
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
            <Head title={t('admin.roles.create')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => router.visit('/admin/roles')}
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.roles.create')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.roles.create_description')}</p>
                    </div>
                </div>

                <Form
                    {...store.form()}
                    className="space-y-6"
                    onSubmit={(e) => {
                        e.preventDefault();
                        const form = e.currentTarget;
                        const formDataObj = new FormData(form);
                        router.post(store().url, formDataObj, {
                            preserveScroll: true,
                        });
                    }}
                >
                    {({ processing, errors }) => {
                        const permissionsContainerRef = useRef<HTMLDivElement>(null);
                        
                        useEffect(() => {
                            if (permissionsContainerRef.current) {
                                permissionsContainerRef.current.innerHTML = '';
                                formData.permissions.forEach(permission => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'permissions[]';
                                    input.value = permission;
                                    permissionsContainerRef.current?.appendChild(input);
                                });
                            }
                        }, [formData.permissions]);
                        
                        return (
                            <>
                                <input type="hidden" name="guard_name" value={formData.guard_name} />
                                <div ref={permissionsContainerRef} />
                                
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
                                                name="name"
                                                defaultValue={formData.name}
                                                placeholder="e.g. manager"
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
                                                    name="icon"
                                                    defaultValue={formData.icon}
                                                    placeholder="heroicon-o-user"
                                                />
                                                <InputError message={errors.icon} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="color">{t('admin.roles.form.color')}</Label>
                                                <Input
                                                    id="color"
                                                    name="color"
                                                    type="color"
                                                    defaultValue={formData.color || '#000000'}
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
                                                        checked={categoryPermissions.every(p => formData.permissions.includes(p.name))}
                                                        onCheckedChange={() => toggleCategory(category)}
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
                                                                checked={formData.permissions.includes(permission.name)}
                                                                onCheckedChange={() => togglePermission(permission.name)}
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
                                        onClick={() => router.visit(index().url)}
                                    >
                                        {t('common.actions.cancel')}
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('common.status.saving') : t('common.actions.create')}
                                    </Button>
                                </div>
                            </>
                        );
                    }}
                </Form>
            </div>
            </div>
        </AdminLayout>
    );
}
