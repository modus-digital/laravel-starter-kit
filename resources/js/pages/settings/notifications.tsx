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
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, router } from '@inertiajs/react';
import { AtSign, Bell, CheckCircle, MessageSquare, Shield, UserPlus } from 'lucide-react';
import { useState } from 'react';

type DeliveryMethod = 'none' | 'email' | 'push' | 'email_push';

type NotificationPreferences = {
    mentions: DeliveryMethod;
    direct_messages: DeliveryMethod;
    comments: DeliveryMethod;
    reminders: DeliveryMethod;
    security_alerts: DeliveryMethod;
    team_invites: DeliveryMethod;
};

type NotificationsSettingsProps = {
    preferences?: Partial<NotificationPreferences>;
};

const defaultPreferences: NotificationPreferences = {
    mentions: 'email_push',
    direct_messages: 'push',
    comments: 'email',
    reminders: 'push',
    security_alerts: 'email_push',
    team_invites: 'email',
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

        router.post('/settings/notifications', settings, {
            preserveScroll: true,
            onSuccess: () => {
                setRecentlySuccessful(true);
                setTimeout(() => setRecentlySuccessful(false), 2000);
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
                                <NotificationTypeItem
                                    id="mentions"
                                    icon={<AtSign className="size-4" />}
                                    title="Mentions"
                                    description="When someone mentions you in a comment or post"
                                    value={settings.mentions}
                                    onChange={(value) => updateSetting('mentions', value)}
                                />
                                <NotificationTypeItem
                                    id="direct_messages"
                                    icon={<MessageSquare className="size-4" />}
                                    title="Direct messages"
                                    description="When you receive a new direct message"
                                    value={settings.direct_messages}
                                    onChange={(value) => updateSetting('direct_messages', value)}
                                />
                                <NotificationTypeItem
                                    id="comments"
                                    icon={<MessageSquare className="size-4" />}
                                    title="Comments"
                                    description="When someone comments on your content"
                                    value={settings.comments}
                                    onChange={(value) => updateSetting('comments', value)}
                                />
                                <NotificationTypeItem
                                    id="reminders"
                                    icon={<CheckCircle className="size-4" />}
                                    title="Reminders"
                                    description="Task and event reminders you've set"
                                    value={settings.reminders}
                                    onChange={(value) => updateSetting('reminders', value)}
                                />
                                <NotificationTypeItem
                                    id="security_alerts"
                                    icon={<Shield className="size-4" />}
                                    title="Security alerts"
                                    description="Important security updates and suspicious activity"
                                    value={settings.security_alerts}
                                    onChange={(value) => updateSetting('security_alerts', value)}
                                />
                                <NotificationTypeItem
                                    id="team_invites"
                                    icon={<UserPlus className="size-4" />}
                                    title="Team invites"
                                    description="When you're invited to join a team or project"
                                    value={settings.team_invites}
                                    onChange={(value) => updateSetting('team_invites', value)}
                                />
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
