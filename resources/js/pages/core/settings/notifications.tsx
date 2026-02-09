import { update } from '@/routes/notifications';
import HeadingSmall from '@/shared/components/heading-small';
import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Label } from '@/shared/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import { Separator } from '@/shared/components/ui/separator';
import AppLayout from '@/shared/layouts/app-layout';
import SettingsLayout from '@/shared/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, router } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

type DeliveryMethod = 'none' | 'email' | 'push' | 'email_push';

type NotificationPreferences = {
    security_alerts: DeliveryMethod;
    comments: DeliveryMethod;
    tasks: DeliveryMethod;
};

type NotificationsSettingsProps = {
    preferences?: Partial<NotificationPreferences>;
};

const defaultPreferences: NotificationPreferences = {
    security_alerts: 'email',
    comments: 'email_push',
    tasks: 'email_push',
};

const notificationLabels: Record<keyof NotificationPreferences, string> = {
    security_alerts: 'settings.notifications.security_alerts.label',
    comments: 'settings.notifications.comments.label',
    tasks: 'settings.notifications.tasks.label',
};

const notificationDescriptions: Record<keyof NotificationPreferences, string> = {
    security_alerts: 'settings.notifications.security_alerts.description',
    comments: 'settings.notifications.comments.description',
    tasks: 'settings.notifications.tasks.description',
};

const deliveryOptions: { value: DeliveryMethod; label: string }[] = [
    { value: 'none', label: 'settings.notifications.delivery.none' },
    { value: 'email', label: 'settings.notifications.delivery.email' },
    { value: 'push', label: 'settings.notifications.delivery.push' },
    { value: 'email_push', label: 'settings.notifications.delivery.email_push' },
];

type NotificationTypeItemProps = {
    id: keyof NotificationPreferences;
    icon: React.ReactNode;
    title: string;
    description: string;
    value: DeliveryMethod;
    onChange: (value: DeliveryMethod) => void;
};

function NotificationTypeItem({ id, icon, title, description, value, onChange }: NotificationTypeItemProps) {
    const { t } = useTranslation();

    return (
        <div className="flex items-center gap-4 py-4">
            <div className="flex min-w-0 flex-1 items-start gap-3">
                <div className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">{icon}</div>
                <div className="min-w-0 flex-1 space-y-0.5">
                    <Label htmlFor={id} className="text-sm leading-none font-medium">
                        {title}
                    </Label>
                    <p className="line-clamp-2 text-sm text-muted-foreground">{description}</p>
                </div>
            </div>
            <Select value={value} onValueChange={(v) => onChange(v as DeliveryMethod)}>
                <SelectTrigger id={id} className="w-36 shrink-0">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {deliveryOptions.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {t(option.label as never)}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}

export default function NotificationsSettings({ preferences }: NotificationsSettingsProps) {
    const { t } = useTranslation();
    const translate = (key: string) => t(key as never);

    const translatedBreadcrumbs: BreadcrumbItem[] = [
        {
            title: translate('settings.notifications.title'),
            href: '/settings/notifications',
        },
    ];

    const [settings, setSettings] = useState<NotificationPreferences>({
        ...defaultPreferences,
        ...preferences,
    });
    const [processing, setProcessing] = useState(false);
    const [recentlySuccessful, setRecentlySuccessful] = useState(false);

    const updateSetting = (key: keyof NotificationPreferences, value: DeliveryMethod) => {
        setSettings((prev) => ({ ...prev, [key]: value }));
    };

    const handleSave = () => {
        setProcessing(true);

        router.put(
            update().url,
            { notifications: settings },
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setRecentlySuccessful(true);
                    setTimeout(() => setRecentlySuccessful(false), 2000);

                    const { data } = page.props as unknown as SharedData;
                    const toastData = (data?.toast ?? {}) as {
                        title?: string;
                        description?: string;
                        type?: string;
                    };

                    if (toastData.title) {
                        toast.success(toastData.title, {
                            description: toastData.description,
                        });
                    }
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={translatedBreadcrumbs}>
            <Head title={translate('settings.notifications.page_title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={translate('settings.notifications.title')} description={translate('settings.notifications.subtitle')} />

                    <Card>
                        <CardHeader className="pb-4">
                            <div className="flex items-center gap-2">
                                <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Bell className="size-5" />
                                </div>
                                <div>
                                    <CardTitle className="text-base">{translate('settings.notifications.card_title')}</CardTitle>
                                    <CardDescription>{translate('settings.notifications.card_description')}</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-2">
                            <div className="divide-y">
                                {(Object.entries(defaultPreferences) as [keyof NotificationPreferences, DeliveryMethod][]).map(([key]) => (
                                    <NotificationTypeItem
                                        key={key}
                                        id={key}
                                        icon={<Bell className="size-5" />}
                                        title={translate(notificationLabels[key])}
                                        description={translate(notificationDescriptions[key])}
                                        value={settings[key]}
                                        onChange={(value) => updateSetting(key, value)}
                                    />
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Save Button */}
                    <div className="flex items-center gap-4">
                        <Button onClick={handleSave} disabled={processing}>
                            {processing ? translate('common.saving') : translate('settings.notifications.save')}
                        </Button>

                        <Transition
                            show={recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-neutral-600">{translate('common.saved')}</p>
                        </Transition>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
