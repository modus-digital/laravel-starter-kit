import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Separator } from '@/shared/components/ui/separator';
import { Switch } from '@/shared/components/ui/switch';
import AdminLayout from '@/shared/layouts/admin/layout';
import { cn } from '@/shared/lib/utils';
import { type SharedData } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { BarChart3, CheckCircle2, Cloud, Eye, EyeOff, Loader2, Users, XCircle } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

type Integrations = {
    mailgun: {
        webhook_signing_key: string | null;
    };
    oauth: {
        google: {
            enabled: boolean;
            client_id: string | null;
            client_secret: string | null;
            available: boolean;
        };
        github: {
            enabled: boolean;
            client_id: string | null;
            client_secret: string | null;
            available: boolean;
        };
        microsoft: {
            enabled: boolean;
            client_id: string | null;
            client_secret: string | null;
            available: boolean;
        };
    };
    s3: {
        enabled: boolean;
        key: string | null;
        secret: string | null;
        bucket: string | null;
        region: string | null;
        endpoint: string | null;
        url: string | null;
        use_path_style_endpoint: boolean;
    };
};

type PageProps = SharedData & {
    integrations: Integrations;
};

type SidebarItem = {
    id: string;
    label: string;
    group: string;
    icon?: React.ElementType;
};

export default function Edit({ integrations }: PageProps) {
    const { t } = useTranslation();

    const [activeSection, setActiveSection] = useState('mailgun');
    const [showSecrets, setShowSecrets] = useState({
        mailgun: false,
        google: false,
        github: false,
        microsoft: false,
        s3: false,
    });
    const [testingS3, setTestingS3] = useState(false);
    const [s3TestResult, setS3TestResult] = useState<{
        success: boolean;
        message: string;
    } | null>(null);

    const { data, setData, put, processing, errors } = useForm({
        mailgun_webhook_signing_key: integrations.mailgun.webhook_signing_key || '',
        google_enabled: integrations.oauth.google.enabled || false,
        google_client_id: integrations.oauth.google.client_id || '',
        google_client_secret: integrations.oauth.google.client_secret || '',
        github_enabled: integrations.oauth.github.enabled || false,
        github_client_id: integrations.oauth.github.client_id || '',
        github_client_secret: integrations.oauth.github.client_secret || '',
        microsoft_enabled: integrations.oauth.microsoft.enabled || false,
        microsoft_client_id: integrations.oauth.microsoft.client_id || '',
        microsoft_client_secret: integrations.oauth.microsoft.client_secret || '',
        s3_enabled: integrations.s3.enabled || false,
        s3_key: integrations.s3.key || '',
        s3_secret: integrations.s3.secret || '',
        s3_bucket: integrations.s3.bucket || '',
        s3_region: integrations.s3.region || '',
        s3_endpoint: integrations.s3.endpoint || '',
        s3_url: integrations.s3.url || '',
        s3_use_path_style_endpoint: integrations.s3.use_path_style_endpoint || false,
    });

    const sidebarItems: SidebarItem[] = useMemo(() => {
        const items: SidebarItem[] = [
            {
                id: 'mailgun',
                label: t('admin.integrations.tabs.mailgun'),
                group: t('admin.integrations.groups.integrations', 'Integrations'),
                icon: BarChart3,
            },
        ];

        // Only add OAuth providers that are available in config
        if (integrations.oauth.google.available) {
            items.push({
                id: 'google',
                label: t('admin.integrations.tabs.google'),
                group: t('admin.integrations.groups.auth_providers', 'Auth Providers'),
                icon: Users,
            });
        }

        if (integrations.oauth.github.available) {
            items.push({
                id: 'github',
                label: t('admin.integrations.tabs.github'),
                group: t('admin.integrations.groups.auth_providers', 'Auth Providers'),
                icon: Users,
            });
        }

        if (integrations.oauth.microsoft.available) {
            items.push({
                id: 'microsoft',
                label: t('admin.integrations.tabs.microsoft'),
                group: t('admin.integrations.groups.auth_providers', 'Auth Providers'),
                icon: Users,
            });
        }

        // Always show S3 storage option
        items.push({
            id: 's3',
            label: t('admin.integrations.tabs.s3', 'S3 Storage'),
            group: t('admin.integrations.groups.integrations', 'Integrations'),
            icon: Cloud,
        });

        return items;
    }, [integrations, t]);

    const groupedItems = sidebarItems.reduce<Record<string, SidebarItem[]>>((groups, item) => {
        if (!groups[item.group]) {
            groups[item.group] = [];
        }
        groups[item.group].push(item);
        return groups;
    }, {});

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/admin/integrations');
    };

    const toggleSecret = (provider: keyof typeof showSecrets) => {
        setShowSecrets((prev) => ({ ...prev, [provider]: !prev[provider] }));
    };

    const testS3Connection = async () => {
        setTestingS3(true);
        setS3TestResult(null);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/admin/integrations/test-s3', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    s3_key: data.s3_key,
                    s3_secret: data.s3_secret,
                    s3_region: data.s3_region,
                    s3_bucket: data.s3_bucket,
                    s3_endpoint: data.s3_endpoint,
                    s3_use_path_style_endpoint: data.s3_use_path_style_endpoint,
                }),
            });

            const result = await response.json();

            if (response.ok) {
                setS3TestResult({
                    success: true,
                    message: result.message || t('admin.integrations.s3.test_connection_success', 'Connection successful!'),
                });
            } else {
                // Build detailed error message with debug info
                let errorMsg =
                    result.message || t('admin.integrations.s3.test_connection_error', 'Connection failed. Please check your credentials.');

                if (result.debug) {
                    if (typeof result.debug === 'string') {
                        errorMsg += `\n\n${result.debug}`;
                    } else if (result.debug.error) {
                        errorMsg += `\n\nError: ${result.debug.error}`;
                        if (result.debug.config) {
                            errorMsg += `\n\nConfiguration:\n`;
                            errorMsg += `- Endpoint: ${result.debug.config.endpoint || 'default'}\n`;
                            errorMsg += `- Region: ${result.debug.config.region}\n`;
                            errorMsg += `- Bucket: ${result.debug.config.bucket}\n`;
                            errorMsg += `- Path Style: ${result.debug.config.path_style ? 'enabled' : 'disabled'}`;
                        }
                    }
                }

                setS3TestResult({
                    success: false,
                    message: errorMsg,
                });
            }
        } catch (error: any) {
            setS3TestResult({
                success: false,
                message: t('admin.integrations.s3.test_connection_error', 'Connection failed. Please check your credentials.'),
            });
        } finally {
            setTestingS3(false);
        }
    };

    return (
        <AdminLayout>
            <Head title={t('admin.integrations.title')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.integrations.title')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.integrations.description')}</p>
                    </div>

                    <div className="flex flex-col lg:flex-row lg:gap-8">
                        {/* Sidebar */}
                        <aside className="mb-6 w-full lg:mb-0 lg:w-56">
                            <nav className="flex flex-col space-y-6">
                                {Object.entries(groupedItems).map(([groupName, items]) => (
                                    <div key={groupName} className="flex flex-col space-y-1">
                                        <div className="px-3 py-1 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                            {groupName}
                                        </div>
                                        {items.map((item) => {
                                            const Icon = item.icon;
                                            return (
                                                <button
                                                    key={item.id}
                                                    type="button"
                                                    onClick={() => setActiveSection(item.id)}
                                                    className={cn(
                                                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                                        'hover:bg-muted hover:text-foreground',
                                                        activeSection === item.id ? 'bg-muted text-foreground' : 'text-muted-foreground',
                                                    )}
                                                >
                                                    {Icon && <Icon className="h-4 w-4" />}
                                                    {item.label}
                                                </button>
                                            );
                                        })}
                                    </div>
                                ))}
                            </nav>
                        </aside>

                        <Separator className="my-4 lg:hidden" />

                        {/* Content */}
                        <form onSubmit={handleSubmit} className="max-w-2xl flex-1 space-y-6">
                            <div className="space-y-6">
                                {/* Mailgun Section */}
                                {activeSection === 'mailgun' && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>{t('admin.integrations.mailgun.title')}</CardTitle>
                                            <CardDescription>{t('admin.integrations.mailgun.description')}</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="mailgun_webhook_signing_key">
                                                    {t('admin.integrations.mailgun.webhook_signing_key')}
                                                </Label>
                                                <div className="relative">
                                                    <Input
                                                        id="mailgun_webhook_signing_key"
                                                        type={showSecrets.mailgun ? 'text' : 'password'}
                                                        value={data.mailgun_webhook_signing_key}
                                                        onChange={(e) => setData('mailgun_webhook_signing_key', e.target.value)}
                                                        placeholder={t('admin.integrations.mailgun.webhook_signing_key_placeholder')}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleSecret('mailgun')}
                                                        className="absolute top-1/2 right-3 -translate-y-1/2"
                                                    >
                                                        {showSecrets.mailgun ? (
                                                            <EyeOff className="h-4 w-4 text-muted-foreground" />
                                                        ) : (
                                                            <Eye className="h-4 w-4 text-muted-foreground" />
                                                        )}
                                                    </button>
                                                </div>
                                                <p className="text-xs text-muted-foreground">
                                                    {t('admin.integrations.mailgun.webhook_signing_key_helper')}
                                                </p>
                                                <InputError message={errors.mailgun_webhook_signing_key} />
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* Google Section */}
                                {activeSection === 'google' && integrations.oauth.google.available && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>{t('admin.integrations.google.title')}</CardTitle>
                                            <CardDescription>{t('admin.integrations.google.description')}</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <Label htmlFor="google_enabled" className="text-base">
                                                        {t('admin.integrations.oauth.enable_provider', 'Enable Provider')}
                                                    </Label>
                                                    <p className="text-sm text-muted-foreground">
                                                        {t(
                                                            'admin.integrations.oauth.enable_provider_description',
                                                            'Allow users to sign in with this provider',
                                                        )}
                                                    </p>
                                                </div>
                                                <Switch
                                                    id="google_enabled"
                                                    checked={data.google_enabled}
                                                    onCheckedChange={(checked) => setData('google_enabled', checked)}
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="google_client_id">{t('admin.integrations.oauth.client_id')}</Label>
                                                <Input
                                                    id="google_client_id"
                                                    value={data.google_client_id}
                                                    onChange={(e) => setData('google_client_id', e.target.value)}
                                                    placeholder={t('admin.integrations.oauth.client_id_placeholder')}
                                                />
                                                <InputError message={errors.google_client_id} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="google_client_secret">{t('admin.integrations.oauth.client_secret')}</Label>
                                                <div className="relative">
                                                    <Input
                                                        id="google_client_secret"
                                                        type={showSecrets.google ? 'text' : 'password'}
                                                        value={data.google_client_secret}
                                                        onChange={(e) => setData('google_client_secret', e.target.value)}
                                                        placeholder={t('admin.integrations.oauth.client_secret_placeholder')}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleSecret('google')}
                                                        className="absolute top-1/2 right-3 -translate-y-1/2"
                                                    >
                                                        {showSecrets.google ? (
                                                            <EyeOff className="h-4 w-4 text-muted-foreground" />
                                                        ) : (
                                                            <Eye className="h-4 w-4 text-muted-foreground" />
                                                        )}
                                                    </button>
                                                </div>
                                                <InputError message={errors.google_client_secret} />
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* GitHub Section */}
                                {activeSection === 'github' && integrations.oauth.github.available && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>{t('admin.integrations.github.title')}</CardTitle>
                                            <CardDescription>{t('admin.integrations.github.description')}</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <Label htmlFor="github_enabled" className="text-base">
                                                        {t('admin.integrations.oauth.enable_provider', 'Enable Provider')}
                                                    </Label>
                                                    <p className="text-sm text-muted-foreground">
                                                        {t(
                                                            'admin.integrations.oauth.enable_provider_description',
                                                            'Allow users to sign in with this provider',
                                                        )}
                                                    </p>
                                                </div>
                                                <Switch
                                                    id="github_enabled"
                                                    checked={data.github_enabled}
                                                    onCheckedChange={(checked) => setData('github_enabled', checked)}
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="github_client_id">{t('admin.integrations.oauth.client_id')}</Label>
                                                <Input
                                                    id="github_client_id"
                                                    value={data.github_client_id}
                                                    onChange={(e) => setData('github_client_id', e.target.value)}
                                                    placeholder={t('admin.integrations.oauth.client_id_placeholder')}
                                                />
                                                <InputError message={errors.github_client_id} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="github_client_secret">{t('admin.integrations.oauth.client_secret')}</Label>
                                                <div className="relative">
                                                    <Input
                                                        id="github_client_secret"
                                                        type={showSecrets.github ? 'text' : 'password'}
                                                        value={data.github_client_secret}
                                                        onChange={(e) => setData('github_client_secret', e.target.value)}
                                                        placeholder={t('admin.integrations.oauth.client_secret_placeholder')}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleSecret('github')}
                                                        className="absolute top-1/2 right-3 -translate-y-1/2"
                                                    >
                                                        {showSecrets.github ? (
                                                            <EyeOff className="h-4 w-4 text-muted-foreground" />
                                                        ) : (
                                                            <Eye className="h-4 w-4 text-muted-foreground" />
                                                        )}
                                                    </button>
                                                </div>
                                                <InputError message={errors.github_client_secret} />
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* Microsoft Section */}
                                {activeSection === 'microsoft' && integrations.oauth.microsoft.available && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>{t('admin.integrations.microsoft.title')}</CardTitle>
                                            <CardDescription>{t('admin.integrations.microsoft.description')}</CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <Label htmlFor="microsoft_enabled" className="text-base">
                                                        {t('admin.integrations.oauth.enable_provider', 'Enable Provider')}
                                                    </Label>
                                                    <p className="text-sm text-muted-foreground">
                                                        {t(
                                                            'admin.integrations.oauth.enable_provider_description',
                                                            'Allow users to sign in with this provider',
                                                        )}
                                                    </p>
                                                </div>
                                                <Switch
                                                    id="microsoft_enabled"
                                                    checked={data.microsoft_enabled}
                                                    onCheckedChange={(checked) => setData('microsoft_enabled', checked)}
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="microsoft_client_id">{t('admin.integrations.oauth.client_id')}</Label>
                                                <Input
                                                    id="microsoft_client_id"
                                                    value={data.microsoft_client_id}
                                                    onChange={(e) => setData('microsoft_client_id', e.target.value)}
                                                    placeholder={t('admin.integrations.oauth.client_id_placeholder')}
                                                />
                                                <InputError message={errors.microsoft_client_id} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="microsoft_client_secret">{t('admin.integrations.oauth.client_secret')}</Label>
                                                <div className="relative">
                                                    <Input
                                                        id="microsoft_client_secret"
                                                        type={showSecrets.microsoft ? 'text' : 'password'}
                                                        value={data.microsoft_client_secret}
                                                        onChange={(e) => setData('microsoft_client_secret', e.target.value)}
                                                        placeholder={t('admin.integrations.oauth.client_secret_placeholder')}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleSecret('microsoft')}
                                                        className="absolute top-1/2 right-3 -translate-y-1/2"
                                                    >
                                                        {showSecrets.microsoft ? (
                                                            <EyeOff className="h-4 w-4 text-muted-foreground" />
                                                        ) : (
                                                            <Eye className="h-4 w-4 text-muted-foreground" />
                                                        )}
                                                    </button>
                                                </div>
                                                <InputError message={errors.microsoft_client_secret} />
                                            </div>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* S3 Section */}
                                {activeSection === 's3' && (
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>{t('admin.integrations.s3.title', 'Amazon S3')}</CardTitle>
                                            <CardDescription>
                                                {t('admin.integrations.s3.description', 'Configure Amazon S3 for cloud file storage')}
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="flex items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <Label htmlFor="s3_enabled" className="text-base">
                                                        {t('admin.integrations.s3.enable', 'Enable S3')}
                                                    </Label>
                                                    <p className="text-sm text-muted-foreground">
                                                        {t(
                                                            'admin.integrations.s3.enable_description',
                                                            'Use Amazon S3 for file storage instead of local disk',
                                                        )}
                                                    </p>
                                                </div>
                                                <Switch
                                                    id="s3_enabled"
                                                    checked={data.s3_enabled}
                                                    onCheckedChange={(checked) => setData('s3_enabled', checked)}
                                                />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="s3_key">{t('admin.integrations.s3.key', 'Access Key ID')}</Label>
                                                <Input
                                                    id="s3_key"
                                                    value={data.s3_key}
                                                    onChange={(e) => setData('s3_key', e.target.value)}
                                                    placeholder={t('admin.integrations.s3.key_placeholder', 'AKIAIOSFODNN7EXAMPLE')}
                                                    disabled={!data.s3_enabled}
                                                />
                                                <InputError message={errors.s3_key} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="s3_secret">{t('admin.integrations.s3.secret', 'Secret Access Key')}</Label>
                                                <div className="relative">
                                                    <Input
                                                        id="s3_secret"
                                                        type={showSecrets.s3 ? 'text' : 'password'}
                                                        value={data.s3_secret}
                                                        onChange={(e) => setData('s3_secret', e.target.value)}
                                                        placeholder={t(
                                                            'admin.integrations.s3.secret_placeholder',
                                                            'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                                                        )}
                                                        disabled={!data.s3_enabled}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleSecret('s3')}
                                                        className="absolute top-1/2 right-3 -translate-y-1/2"
                                                        disabled={!data.s3_enabled}
                                                    >
                                                        {showSecrets.s3 ? (
                                                            <EyeOff className="h-4 w-4 text-muted-foreground" />
                                                        ) : (
                                                            <Eye className="h-4 w-4 text-muted-foreground" />
                                                        )}
                                                    </button>
                                                </div>
                                                <InputError message={errors.s3_secret} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="s3_region">{t('admin.integrations.s3.region', 'Region')}</Label>
                                                <Input
                                                    id="s3_region"
                                                    value={data.s3_region}
                                                    onChange={(e) => setData('s3_region', e.target.value)}
                                                    placeholder={t('admin.integrations.s3.region_placeholder', 'us-east-1')}
                                                    disabled={!data.s3_enabled}
                                                />
                                                <p className="text-xs text-muted-foreground">
                                                    {t('admin.integrations.s3.region_helper', 'The AWS region where your bucket is located')}
                                                </p>
                                                <InputError message={errors.s3_region} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="s3_bucket">{t('admin.integrations.s3.bucket', 'Bucket Name')}</Label>
                                                <Input
                                                    id="s3_bucket"
                                                    value={data.s3_bucket}
                                                    onChange={(e) => setData('s3_bucket', e.target.value)}
                                                    placeholder={t('admin.integrations.s3.bucket_placeholder', 'my-bucket')}
                                                    disabled={!data.s3_enabled}
                                                />
                                                <InputError message={errors.s3_bucket} />
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="s3_url">{t('admin.integrations.s3.url', 'Url')}</Label>
                                                <Input
                                                    id="s3_url"
                                                    value={data.s3_url}
                                                    onChange={(e) => setData('s3_url', e.target.value)}
                                                    placeholder={t('admin.integrations.s3.url_placeholder', 'http://localhost:9000')}
                                                    disabled={!data.s3_enabled}
                                                />
                                                <p className="text-xs text-muted-foreground">
                                                    {t(
                                                        'admin.integrations.s3.url_helper',
                                                        'Required for MinIO and other S3-compatible services. Leave empty for AWS S3.',
                                                    )}
                                                </p>
                                                <InputError message={errors.s3_url} />
                                            </div>

                                            <div className="flex items-center justify-between rounded-lg border p-4">
                                                <div className="space-y-0.5">
                                                    <Label htmlFor="s3_use_path_style_endpoint" className="text-base">
                                                        {t('admin.integrations.s3.use_path_style', 'Use Path Style Endpoint')}
                                                    </Label>
                                                    <p className="text-sm text-muted-foreground">
                                                        {t(
                                                            'admin.integrations.s3.use_path_style_description',
                                                            'Required for MinIO and most S3-compatible services. Disable only for AWS S3.',
                                                        )}
                                                    </p>
                                                </div>
                                                <Switch
                                                    id="s3_use_path_style_endpoint"
                                                    checked={data.s3_use_path_style_endpoint}
                                                    onCheckedChange={(checked) => setData('s3_use_path_style_endpoint', checked)}
                                                    disabled={!data.s3_enabled}
                                                />
                                            </div>

                                            {data.s3_use_path_style_endpoint && (
                                                <div className="space-y-2">
                                                    <Label htmlFor="s3_endpoint">{t('admin.integrations.s3.endpoint', 'Endpoint')}</Label>
                                                    <Input
                                                        id="s3_endpoint"
                                                        value={data.s3_endpoint}
                                                        onChange={(e) => setData('s3_endpoint', e.target.value)}
                                                        placeholder={t('admin.integrations.s3.endpoint_placeholder', 'http://localhost:9000/bucket-name')}
                                                        disabled={!data.s3_enabled}
                                                    />
                                                    <p className="text-xs text-muted-foreground">
                                                        {t(
                                                            'admin.integrations.s3.endpoint_helper',
                                                            'Public URL for accessing uploaded files. For MinIO: http://localhost:9000/bucket-name',
                                                        )}
                                                    </p>
                                                    <InputError message={errors.s3_endpoint} />
                                                </div>
                                            )}
                                        </CardContent>
                                    </Card>
                                )}
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    {processing ? t('common.saving') : t('common.actions.save')}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
