import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, router, usePage } from '@inertiajs/react';
import { Check, Copy, Key, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import ApiTokenController from '@/actions/App/Http/Controllers/Settings/ApiTokenController';

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
    const page = usePage<
        SharedData & {
            token?: string;
            tokenName?: string;
            status?: string;
        }
    >();

    const [selectedPermissions, setSelectedPermissions] = useState<string[]>(
        [],
    );
    const [showToken, setShowToken] = useState(false);
    const [copiedToken, setCopiedToken] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'API Tokens',
            href: '/settings/api-tokens',
        },
    ];

    useEffect(() => {
        if (page.props.token) {
            setShowToken(true);
        }
    }, [page.props.token]);

    const handlePermissionToggle = (permission: string) => {
        setSelectedPermissions((prev) =>
            prev.includes(permission)
                ? prev.filter((p) => p !== permission)
                : [...prev, permission],
        );
    };

    const handleCopyToken = async () => {
        if (page.props.token) {
            await navigator.clipboard.writeText(page.props.token);
            setCopiedToken(true);
            setTimeout(() => setCopiedToken(false), 2000);
        }
    };

    const handleDeleteToken = (tokenId: string) => {
        if (
            confirm(
                'Are you sure you want to delete this token? This action cannot be undone.',
            )
        ) {
            router.delete(`/settings/api-tokens/${tokenId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="API Tokens" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="API Tokens"
                        description="Manage your API tokens to access the API programmatically."
                    />

                    {showToken && page.props.token && (
                        <Alert className="border-green-500 bg-green-50 dark:bg-green-950">
                            <Key className="h-4 w-4 text-green-600 dark:text-green-400" />
                            <AlertTitle className="text-green-800 dark:text-green-200">
                                Token Created Successfully
                            </AlertTitle>
                            <AlertDescription className="mt-2 space-y-2">
                                <p className="text-sm text-green-700 dark:text-green-300">
                                    Please copy your new API token. For security
                                    reasons, it won't be shown again.
                                </p>
                                <div className="flex items-center gap-2">
                                    <code className="flex-1 rounded bg-green-100 px-3 py-2 text-sm text-green-900 dark:bg-green-900 dark:text-green-100">
                                        {page.props.token}
                                    </code>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={handleCopyToken}
                                        className="shrink-0"
                                    >
                                        {copiedToken ? (
                                            <>
                                                <Check className="h-4 w-4" />
                                                Copied
                                            </>
                                        ) : (
                                            <>
                                                <Copy className="h-4 w-4" />
                                                Copy
                                            </>
                                        )}
                                    </Button>
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    <div className="space-y-6">
                        <HeadingSmall
                            title="Create New Token"
                            description="Generate a new API token with specific permissions."
                        />

                        <Form
                            {...ApiTokenController.store.form()}
                            disableWhileProcessing
                            onSuccess={() => setSelectedPermissions([])}
                            options={{ preserveScroll: true }}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">
                                            Token Name
                                        </Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            placeholder="e.g., Mobile App Token"
                                            required
                                        />
                                        <InputError
                                            className="mt-2"
                                            message={errors.name}
                                        />
                                    </div>

                                    <div className="grid gap-4">
                                        <Label>Permissions</Label>
                                        <p className="text-sm text-muted-foreground">
                                            Select the permissions this token
                                            should have. You can only assign
                                            permissions that you currently
                                            possess.
                                        </p>

                                        <div className="space-y-3">
                                            {availablePermissions.map(
                                                (permission) => {
                                                    const hasPermission =
                                                        userPermissions.includes(
                                                            permission.value,
                                                        );
                                                    const isChecked =
                                                        selectedPermissions.includes(
                                                            permission.value,
                                                        );

                                                    return (
                                                        <div
                                                            key={
                                                                permission.value
                                                            }
                                                            className="flex items-start space-x-3 rounded-lg border p-4"
                                                        >
                                                            <Checkbox
                                                                id={
                                                                    permission.value
                                                                }
                                                                name="permissions[]"
                                                                value={
                                                                    permission.value
                                                                }
                                                                checked={
                                                                    isChecked
                                                                }
                                                                disabled={
                                                                    !hasPermission
                                                                }
                                                                onCheckedChange={() =>
                                                                    handlePermissionToggle(
                                                                        permission.value,
                                                                    )
                                                                }
                                                            />
                                                            <div className="flex-1 space-y-1">
                                                                <Label
                                                                    htmlFor={
                                                                        permission.value
                                                                    }
                                                                    className={
                                                                        !hasPermission
                                                                            ? 'text-muted-foreground'
                                                                            : ''
                                                                    }
                                                                >
                                                                    {
                                                                        permission.label
                                                                    }
                                                                    {!hasPermission && (
                                                                        <span className="ml-2 text-xs text-muted-foreground">
                                                                            (Not
                                                                            available)
                                                                        </span>
                                                                    )}
                                                                </Label>
                                                                <p className="text-sm text-muted-foreground">
                                                                    {
                                                                        permission.description
                                                                    }
                                                                </p>
                                                            </div>
                                                        </div>
                                                    );
                                                },
                                            )}
                                        </div>

                                        <InputError
                                            className="mt-2"
                                            message={errors.permissions}
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        disabled={
                                            processing ||
                                            selectedPermissions.length === 0
                                        }
                                    >
                                        Create Token
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>

                    <div className="space-y-6">
                        <HeadingSmall
                            title="Existing Tokens"
                            description="View and manage your existing API tokens."
                        />

                        {tokens.length === 0 ? (
                            <div className="rounded-lg border border-dashed p-8 text-center">
                                <Key className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">
                                    No API tokens yet
                                </h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Create your first API token to get started.
                                </p>
                            </div>
                        ) : (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Name</TableHead>
                                            <TableHead>Permissions</TableHead>
                                            <TableHead>Last Used</TableHead>
                                            <TableHead>Created</TableHead>
                                            <TableHead className="w-[100px]">
                                                Actions
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {tokens.map((token) => (
                                            <TableRow key={token.id}>
                                                <TableCell className="font-medium">
                                                    {token.name}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex flex-wrap gap-1">
                                                        {token.abilities.map(
                                                            (ability) => (
                                                                <span
                                                                    key={
                                                                        ability
                                                                    }
                                                                    className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                                                >
                                                                    {
                                                                        availablePermissions.find(
                                                                            (
                                                                                p,
                                                                            ) =>
                                                                                p.value ===
                                                                                ability,
                                                                        )
                                                                            ?.label ||
                                                                            ability
                                                                    }
                                                                </span>
                                                            ),
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {token.last_used_at ? (
                                                        <span className="text-sm">
                                                            {new Date(
                                                                token.last_used_at,
                                                            ).toLocaleDateString()}
                                                        </span>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">
                                                            Never
                                                        </span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <span className="text-sm">
                                                        {new Date(
                                                            token.created_at,
                                                        ).toLocaleDateString()}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            handleDeleteToken(
                                                                token.id,
                                                            )
                                                        }
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
