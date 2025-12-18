import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, router } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

type DeliveryMethod = 'none' | 'email' | 'push' | 'email_push';

type NotificationPreferences = {
    security_alerts: DeliveryMethod;
    comments: DeliveryMethod;
};

type NotificationsSettingsProps = {
    preferences?: Partial<NotificationPreferences>;
};

const defaultPreferences: NotificationPreferences = {
    security_alerts: 'email',
    comments: 'email_push',
};

const notificationLabels: Record<keyof NotificationPreferences, string> = {
    security_alerts: 'Security alerts',
    comments: 'Comments',
};

const notificationDescriptions: Record<keyof NotificationPreferences, string> = {
    security_alerts: 'Get notified immediately about security-related activity on your account.',
    comments: 'Receive updates when someone comments on your content.',
};

const deliveryOptions: { value: DeliveryMethod; label: string }[] = [
    { value: 'none', label: 'None' },
    { value: 'email', label: 'Email only' },
    { value: 'push', label: 'Push only' },
    { value: 'email_push', label: 'Email + Push' },
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification Preferences',
        href: '/settings/notifications',
    },
];

type NotificationTypeItemProps = {
    id: keyof NotificationPreferences;
    icon: React.ReactNode;
    title: string;
    description: string;
    value: DeliveryMethod;
    onChange: (value: DeliveryMethod) => void;
};

function NotificationTypeItem({
    id,
    icon,
    title,
    description,
    value,
    onChange,
}: NotificationTypeItemProps) {
    return (
        <div className="flex items-center gap-4 py-4">
            <div className="flex min-w-0 flex-1 items-start gap-3">
                <div className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                    {icon}
                </div>
                <div className="min-w-0 flex-1 space-y-0.5">
                    <Label htmlFor={id} className="text-sm font-medium leading-none">
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
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}

export default function NotificationsSettings({ preferences }: NotificationsSettingsProps) {
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

        router.put('/settings/notifications', { notifications: settings }, {
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
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification Preferences" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Notification Preferences"
                        description="Choose how you want to be notified for each type of activity"
                    />

                    <Card>
                        <CardHeader className="pb-4">
                            <div className="flex items-center gap-2">
                                <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Bell className="size-5" />
                                </div>
                                <div>
                                    <CardTitle className="text-base">Notification Types</CardTitle>
                                    <CardDescription>
                                        Select how you want to receive each type of notification
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-2">
                            <div className="divide-y">
                                {(Object.entries(defaultPreferences) as [ keyof NotificationPreferences, DeliveryMethod ][]).map(([key]) => (
                                    <NotificationTypeItem
                                        key={key}
                                        id={key}
                                        icon={<Bell className="size-5" />}
                                        title={notificationLabels[key]}
                                        description={notificationDescriptions[key]}
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
                            {processing ? 'Saving...' : 'Save preferences'}
                        </Button>

                        <Transition
                            show={recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
