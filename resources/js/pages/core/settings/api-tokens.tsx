import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, router, usePage } from '@inertiajs/react';
import { Check, Copy, Key, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import ApiTokenController from '@/actions/App/Http/Controllers/Settings/ApiTokenController';
import HeadingSmall from '@/shared/components/heading-small';
import InputError from '@/shared/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/shared/components/ui/alert';
import { Button } from '@/shared/components/ui/button';
import { Checkbox } from '@/shared/components/ui/checkbox';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import AppLayout from '@/shared/layouts/app-layout';
import SettingsLayout from '@/shared/layouts/settings/layout';

interface Permission {
    value: string;
    label: string;
    description: string;
}

interface ApiToken {
    id: string;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
}

export default function ApiTokens({
    tokens,
    availablePermissions,
    userPermissions,
}: {
    tokens: ApiToken[];
    availablePermissions: Permission[];
    userPermissions: string[];
}) {
    const { t } = useTranslation();
    const page = usePage<SharedData>();

    const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);
    const [showToken, setShowToken] = useState(false);
    const [copiedToken, setCopiedToken] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.api_tokens.page_title'),
            href: '/settings/api-tokens',
        },
    ];

    // Extract token with proper typing
    const apiToken = page.props.data?.token as string | undefined;

    useEffect(() => {
        if (apiToken) {
            setShowToken(true);
        }
    }, [apiToken]);

    const handlePermissionToggle = (permission: string) => {
        setSelectedPermissions((prev) => (prev.includes(permission) ? prev.filter((p) => p !== permission) : [...prev, permission]));
    };

    const groupPermissionsByResource = (permissions: Permission[]) => {
        const groups: Record<string, Permission[]> = {};

        permissions.forEach((permission) => {
            let groupName = t('settings.api_tokens.groups.general');

            if (permission.value.includes(':users')) {
                groupName = t('settings.api_tokens.groups.users');
            } else if (permission.value.includes(':roles')) {
                groupName = t('settings.api_tokens.groups.roles');
            } else if (permission.value.includes(':api-tokens')) {
                groupName = t('settings.api_tokens.groups.api_tokens');
            } else if (permission.value.includes(':clients')) {
                groupName = t('settings.api_tokens.groups.clients');
            } else if (permission.value.includes(':socialite-providers')) {
                groupName = t('settings.api_tokens.groups.socialite_providers');
            }

            if (!groups[groupName]) {
                groups[groupName] = [];
            }
            groups[groupName].push(permission);
        });

        // Sort permissions within each group (CRUD order: Create, Read, Update, Delete, Restore, then others)
        const sortOrder = ['create:', 'read:', 'update:', 'delete:', 'restore:'];

        Object.keys(groups).forEach((groupName) => {
            groups[groupName].sort((a, b) => {
                const aIndex = sortOrder.findIndex((prefix) => a.value.startsWith(prefix));
                const bIndex = sortOrder.findIndex((prefix) => b.value.startsWith(prefix));

                // If both are CRUD permissions, sort by CRUD order
                if (aIndex !== -1 && bIndex !== -1) {
                    return aIndex - bIndex;
                }

                // If only one is CRUD, put CRUD first
                if (aIndex !== -1) return -1;
                if (bIndex !== -1) return 1;

                // Otherwise, sort alphabetically
                return a.label.localeCompare(b.label);
            });
        });

        // Sort groups by number of permissions (descending - most permissions first)
        const sortedGroups: Record<string, Permission[]> = {};
        Object.keys(groups)
            .sort((a, b) => groups[b].length - groups[a].length)
            .forEach((groupName) => {
                sortedGroups[groupName] = groups[groupName];
            });

        return sortedGroups;
    };

    const getPermissionDisplayLabel = (permission: Permission) => {
        // For CRUD permissions, remove the resource name
        if (permission.value.startsWith('create:')) {
            return t('settings.api_tokens.permissions.display_labels.create');
        }
        if (permission.value.startsWith('read:')) {
            return t('settings.api_tokens.permissions.display_labels.read');
        }
        if (permission.value.startsWith('update:')) {
            return t('settings.api_tokens.permissions.display_labels.update');
        }
        if (permission.value.startsWith('delete:')) {
            return t('settings.api_tokens.permissions.display_labels.delete');
        }
        if (permission.value.startsWith('restore:')) {
            return t('settings.api_tokens.permissions.display_labels.restore');
        }

        // For other permissions, keep the original label
        return permission.label;
    };

    const handleCopyToken = async () => {
        if (apiToken) {
            await navigator.clipboard.writeText(apiToken);
            setCopiedToken(true);
            setTimeout(() => setCopiedToken(false), 2000);
        }
    };

    const handleDeleteToken = (tokenId: string) => {
        if (confirm('Are you sure you want to delete this token? This action cannot be undone.')) {
            router.delete(`/settings/api-tokens/${tokenId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.api_tokens.page_title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={t('settings.api_tokens.title')} description={t('settings.api_tokens.description')} />

                    {showToken && apiToken && (
                        <Alert className="border-green-500 bg-green-50 dark:bg-green-950">
                            <Key className="h-4 w-4 text-green-600 dark:text-green-400" />
                            <AlertTitle className="text-green-800 dark:text-green-200">{t('settings.api_tokens.token_created_title')}</AlertTitle>
                            <AlertDescription className="mt-2 space-y-2">
                                <p className="text-sm text-green-700 dark:text-green-300">{t('settings.api_tokens.token_created_description')}</p>
                                <div className="flex items-center gap-2">
                                    <code className="flex-1 rounded bg-green-100 px-3 py-2 text-sm text-green-900 dark:bg-green-900 dark:text-green-100">
                                        {apiToken}
                                    </code>
                                    <Button size="sm" variant="outline" onClick={handleCopyToken} className="shrink-0">
                                        {copiedToken ? (
                                            <>
                                                <Check className="h-4 w-4" />
                                                {t('settings.api_tokens.actions.copied')}
                                            </>
                                        ) : (
                                            <>
                                                <Copy className="h-4 w-4" />
                                                {t('settings.api_tokens.actions.copy')}
                                            </>
                                        )}
                                    </Button>
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    <div className="space-y-6">
                        <HeadingSmall title={t('settings.api_tokens.create_title')} description={t('settings.api_tokens.create_description')} />

                        <Form
                            {...ApiTokenController.store.form()}
                            disableWhileProcessing
                            onSuccess={() => {
                                setSelectedPermissions([]);
                            }}
                            options={{ preserveScroll: true }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">{t('settings.api_tokens.form.name')}</Label>
                                        <Input id="name" name="name" placeholder={t('settings.api_tokens.form.name_placeholder')} required />
                                        <InputError className="mt-2" message={errors.name} />
                                    </div>

                                    <div className="grid gap-4">
                                        <Label>{t('settings.api_tokens.form.permissions')}</Label>
                                        <p className="text-sm text-muted-foreground">{t('settings.api_tokens.form.permissions_helper')}</p>

                                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                            {Object.entries(
                                                groupPermissionsByResource(
                                                    availablePermissions.filter(
                                                        (p) => !p.value.startsWith('access:') && p.value !== 'manage:settings',
                                                    ),
                                                ),
                                            ).map(([groupName, permissions]) => (
                                                <div key={groupName} className="space-y-3">
                                                    <h4 className="text-sm font-medium text-foreground">{groupName}</h4>
                                                    <div className="space-y-2 pl-4">
                                                        {permissions.map((permission) => {
                                                            const hasPermission = userPermissions.includes(permission.value);
                                                            const isChecked = selectedPermissions.includes(permission.value);

                                                            return (
                                                                <div key={permission.value} className="flex items-center space-x-3">
                                                                    <Checkbox
                                                                        id={permission.value}
                                                                        name="permissions[]"
                                                                        value={permission.value}
                                                                        checked={isChecked}
                                                                        disabled={!hasPermission}
                                                                        onCheckedChange={() => handlePermissionToggle(permission.value)}
                                                                    />
                                                                    <Label
                                                                        htmlFor={permission.value}
                                                                        className={`text-sm ${!hasPermission ? 'text-muted-foreground' : ''}`}
                                                                    >
                                                                        {getPermissionDisplayLabel(permission)}
                                                                        {!hasPermission && (
                                                                            <span className="ml-2 text-xs text-muted-foreground">
                                                                                {t('settings.api_tokens.permissions.not_available')}
                                                                            </span>
                                                                        )}
                                                                    </Label>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>

                                        <InputError className="mt-2" message={errors.permissions} />
                                    </div>

                                    <Button type="submit" disabled={processing || selectedPermissions.length === 0}>
                                        {t('common.actions.save')}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>

                    <div className="space-y-6">
                        <HeadingSmall title={t('settings.api_tokens.existing_title')} description={t('settings.api_tokens.existing_description')} />

                        {tokens.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-8 text-center">
                                <Key className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">{t('settings.api_tokens.empty.title')}</h3>
                                <p className="mt-2 text-sm text-muted-foreground">{t('settings.api_tokens.empty.description')}</p>
                            </div>
                        ) : (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{t('settings.api_tokens.table.name')}</TableHead>
                                            <TableHead>{t('settings.api_tokens.table.permissions')}</TableHead>
                                            <TableHead>{t('settings.api_tokens.table.last_used')}</TableHead>
                                            <TableHead>{t('settings.api_tokens.table.created')}</TableHead>
                                            <TableHead className="w-[100px]">{t('settings.api_tokens.table.actions')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {tokens.map((token) => (
                                            <TableRow key={token.id}>
                                                <TableCell className="font-medium">{token.name}</TableCell>
                                                <TableCell>
                                                    <div className="flex flex-wrap gap-1">
                                                        {token.abilities.map((ability) => (
                                                            <span
                                                                key={ability}
                                                                className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                                            >
                                                                {availablePermissions.find((p) => p.value === ability)?.label || ability}
                                                            </span>
                                                        ))}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {token.last_used_at ? (
                                                        <span className="text-sm">{new Date(token.last_used_at).toLocaleDateString()}</span>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">{t('settings.api_tokens.table.never')}</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <span className="text-sm">{new Date(token.created_at).toLocaleDateString()}</span>
                                                </TableCell>
                                                <TableCell>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => handleDeleteToken(token.id)}
                                                        className="text-destructive hover:text-destructive"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
