import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, router } from '@inertiajs/react';
import { Bell, Mail, MessageSquare, Shield, Sparkles, TrendingUp, Zap } from 'lucide-react';
import { useState } from 'react';

type NotificationPreferences = {
    // Email notifications
    email_marketing: boolean;
    email_security: boolean;
    email_product_updates: boolean;
    email_weekly_digest: boolean;
    // Push notifications
    push_mentions: boolean;
    push_direct_messages: boolean;
    push_comments: boolean;
    push_reminders: boolean;
};

type NotificationsSettingsProps = {
    preferences?: NotificationPreferences;
};

const defaultPreferences: NotificationPreferences = {
    email_marketing: false,
    email_security: true,
    email_product_updates: true,
    email_weekly_digest: false,
    push_mentions: true,
    push_direct_messages: true,
    push_comments: true,
    push_reminders: false,
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification Preferences',
        href: '/settings/notifications',
    },
];

type PreferenceItemProps = {
    id: string;
    icon: React.ReactNode;
    title: string;
    description: string;
    checked: boolean;
    onCheckedChange: (checked: boolean) => void;
};

function PreferenceItem({ id, icon, title, description, checked, onCheckedChange }: PreferenceItemProps) {
    return (
        <div className="flex items-start justify-between gap-4 py-3">
            <div className="flex items-start gap-3">
                <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                    {icon}
                </div>
                <div className="space-y-0.5">
                    <Label htmlFor={id} className="cursor-pointer text-sm font-medium leading-none">
                        {title}
                    </Label>
                    <p className="text-sm text-muted-foreground">{description}</p>
                </div>
            </div>
            <Switch id={id} checked={checked} onCheckedChange={onCheckedChange} />
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

    const updateSetting = <K extends keyof NotificationPreferences>(key: K, value: boolean) => {
        setSettings((prev) => ({ ...prev, [key]: value }));
    };

    const handleSave = () => {
        setProcessing(true);

        // Simulating API call - replace with actual endpoint when available
        router.post(
            '/settings/notifications',
            settings,
            {
                preserveScroll: true,
                onSuccess: () => {
                    setRecentlySuccessful(true);
                    setTimeout(() => setRecentlySuccessful(false), 2000);
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification Preferences" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Notification Preferences"
                        description="Choose how and when you want to be notified"
                    />

                    <div className="grid gap-6 lg:grid-cols-2">
                        {/* Email Notifications */}
                        <Card>
                        <CardHeader className="pb-4">
                            <div className="flex items-center gap-2">
                                <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Mail className="size-5" />
                                </div>
                                <div>
                                    <CardTitle className="text-base">Email Notifications</CardTitle>
                                    <CardDescription>
                                        Manage what emails you receive from us
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-4">
                            <div className="divide-y">
                                <PreferenceItem
                                    id="email_security"
                                    icon={<Shield className="size-4" />}
                                    title="Security alerts"
                                    description="Receive emails about security updates and suspicious activity"
                                    checked={settings.email_security}
                                    onCheckedChange={(checked) => updateSetting('email_security', checked)}
                                />
                                <PreferenceItem
                                    id="email_product_updates"
                                    icon={<Sparkles className="size-4" />}
                                    title="Product updates"
                                    description="Get notified about new features and improvements"
                                    checked={settings.email_product_updates}
                                    onCheckedChange={(checked) => updateSetting('email_product_updates', checked)}
                                />
                                <PreferenceItem
                                    id="email_weekly_digest"
                                    icon={<TrendingUp className="size-4" />}
                                    title="Weekly digest"
                                    description="Receive a weekly summary of your activity and updates"
                                    checked={settings.email_weekly_digest}
                                    onCheckedChange={(checked) => updateSetting('email_weekly_digest', checked)}
                                />
                                <PreferenceItem
                                    id="email_marketing"
                                    icon={<Zap className="size-4" />}
                                    title="Marketing emails"
                                    description="Receive tips, offers, and promotional content"
                                    checked={settings.email_marketing}
                                    onCheckedChange={(checked) => updateSetting('email_marketing', checked)}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Push Notifications */}
                    <Card>
                        <CardHeader className="pb-4">
                            <div className="flex items-center gap-2">
                                <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Bell className="size-5" />
                                </div>
                                <div>
                                    <CardTitle className="text-base">Push Notifications</CardTitle>
                                    <CardDescription>
                                        Control your browser and mobile notifications
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <Separator />
                        <CardContent className="pt-4">
                            <div className="divide-y">
                                <PreferenceItem
                                    id="push_mentions"
                                    icon={<span className="text-sm font-bold">@</span>}
                                    title="Mentions"
                                    description="When someone mentions you in a comment or post"
                                    checked={settings.push_mentions}
                                    onCheckedChange={(checked) => updateSetting('push_mentions', checked)}
                                />
                                <PreferenceItem
                                    id="push_direct_messages"
                                    icon={<MessageSquare className="size-4" />}
                                    title="Direct messages"
                                    description="When you receive a new direct message"
                                    checked={settings.push_direct_messages}
                                    onCheckedChange={(checked) => updateSetting('push_direct_messages', checked)}
                                />
                                <PreferenceItem
                                    id="push_comments"
                                    icon={<MessageSquare className="size-4" />}
                                    title="Comments"
                                    description="When someone comments on your content"
                                    checked={settings.push_comments}
                                    onCheckedChange={(checked) => updateSetting('push_comments', checked)}
                                />
                                <PreferenceItem
                                    id="push_reminders"
                                    icon={<Bell className="size-4" />}
                                    title="Reminders"
                                    description="Task and event reminders you've set"
                                    checked={settings.push_reminders}
                                    onCheckedChange={(checked) => updateSetting('push_reminders', checked)}
                                />
                            </div>
                        </CardContent>
                    </Card>
                    </div>

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

