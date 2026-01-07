import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import type { Notification as NotificationType } from '@/types/models';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Bell, Calendar, CheckCircle, Clock, ExternalLink, Mail, MessageSquare, Trash2, User, Flag } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type NotificationProps = {
    notification: NotificationType;
};

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatTime(dateString: string): string {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatRelativeTime(dateString: string, t: (key: string, options?: { count?: number }) => string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) {
        return t('notifications.relative_time.just_now');
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return t(diffInMinutes === 1 ? 'notifications.relative_time.minutes_ago' : 'notifications.relative_time.minutes_ago_plural', { count: diffInMinutes });
    }

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return t(diffInHours === 1 ? 'notifications.relative_time.hours_ago' : 'notifications.relative_time.hours_ago_plural', { count: diffInHours });
    }

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) {
        return t(diffInDays === 1 ? 'notifications.relative_time.days_ago' : 'notifications.relative_time.days_ago_plural', { count: diffInDays });
    }

    return formatDate(dateString);
}

export default function Notification({ notification }: NotificationProps) {
    const { t } = useTranslation();
    
    // Helper function to translate notification text (handles both translation keys and plain text)
    const translateNotificationText = (text: string | null | undefined, replacements?: Record<string, unknown> | null): string => {
        if (!text) return '';
        // If it looks like a translation key (starts with "notifications."), try to translate it
        if (text.startsWith('notifications.')) {
            // Merge replacements with defaultValue option
            const translated = t(text as never, { ...(replacements || {}), defaultValue: text });
            // If translation returns the same value, it might be missing - return as-is
            return translated !== text ? translated : text;
        }
        return text;
    };

    const translatedTitle = translateNotificationText(notification.title, notification.translation_replacements);
    const translatedBody = translateNotificationText(notification.body, notification.translation_replacements);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('notifications.title'),
            href: '/notifications',
        },
        {
            title: translatedTitle,
            href: `/notifications/${notification.id}`,
        },
    ];

    const isRead = notification.read_at !== null && notification.read_at !== undefined;

    const markAsRead = () => {
        router.post(`/notifications/${notification.id}/read`, undefined, {
            preserveScroll: true,
        });
    };

    const markAsUnread = () => {
        router.post(`/notifications/${notification.id}/unread`, undefined, {
            preserveScroll: true,
        });
    };

    const deleteNotification = () => {
        router.delete(`/notifications/${notification.id}`, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={translatedTitle} />

            <div className="mx-auto my-8 flex w-full max-w-4xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
                {/* Back Button */}
                <div className="flex items-center">
                    <Button variant="ghost" size="sm" asChild className="gap-2">
                        <Link href="/notifications">
                            <ArrowLeft className="size-4" />
                            Back to notifications
                        </Link>
                    </Button>
                </div>

                {/* Main Notification Card */}
                <Card className="overflow-hidden">
                    <CardHeader className="pb-4">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div className="flex items-start gap-4">
                                <div
                                    className={`flex size-12 shrink-0 items-center justify-center rounded-full ${
                                        isRead ? 'bg-muted text-muted-foreground' : 'bg-primary/10 text-primary'
                                    }`}
                                >
                                    <Bell className="size-6" />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge variant={isRead ? 'outline' : 'default'}>
                                            {isRead ? (
                                                <>
                                                    <CheckCircle className="size-3" />
                                                    Read
                                                </>
                                            ) : (
                                                <>
                                                    <Mail className="size-3" />
                                                    Unread
                                                </>
                                            )}
                                        </Badge>
                                        {notification.deleted_at && <Badge variant="destructive">Deleted</Badge>}
                                    </div>
                                    <CardTitle className="text-xl sm:text-2xl">{translatedTitle}</CardTitle>
                                    <CardDescription className="flex items-center gap-1.5">
                                        <Clock className="size-3.5" />
                                        {formatRelativeTime(notification.created_at, t)}
                                    </CardDescription>
                                </div>
                            </div>

                            {/* Action Buttons - Desktop */}
                            <div className="hidden shrink-0 gap-2 sm:flex">
                                {isRead ? (
                                    <Button variant="outline" size="sm" onClick={markAsUnread} className="gap-1.5">
                                        <Mail className="size-4" />
                                        Mark unread
                                    </Button>
                                ) : (
                                    <Button variant="outline" size="sm" onClick={markAsRead} className="gap-1.5">
                                        <CheckCircle className="size-4" />
                                        Mark read
                                    </Button>
                                )}
                                <Button variant="destructive" size="sm" onClick={deleteNotification} className="gap-1.5">
                                    <Trash2 className="size-4" />
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </CardHeader>

                    <Separator />

                    <CardContent className="py-6">
                        <div className="prose prose-sm dark:prose-invert max-w-none">
                            <p className="leading-relaxed whitespace-pre-wrap text-foreground">{translatedBody}</p>
                        </div>

                        {/* Context Section */}
                        {notification.context && (
                            <div className="mt-6 space-y-4">
                                {notification.context.type === 'comment' && notification.context.comment_preview && (
                                    <div className="rounded-lg border bg-muted/50 p-4">
                                        <div className="mb-2 flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                            <MessageSquare className="size-4" />
                                            Comment Preview
                                        </div>
                                        <blockquote className="border-l-4 border-primary pl-4 italic text-foreground">
                                            {notification.context.comment_preview}
                                        </blockquote>
                                        {notification.context.task_title && (
                                            <div className="mt-3 text-xs text-muted-foreground">
                                                Task: {notification.context.task_title}
                                            </div>
                                        )}
                                    </div>
                                )}

                                {notification.context.type === 'task' && (
                                    <div className="rounded-lg border bg-muted/50 p-4">
                                        <div className="mb-3 flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                            <Calendar className="size-4" />
                                            Task Details
                                        </div>
                                        <div className="space-y-3">
                                            {notification.context.task_title && (
                                                <div>
                                                    <div className="text-xs font-medium text-muted-foreground">Title</div>
                                                    <div className="text-sm font-medium text-foreground">{notification.context.task_title}</div>
                                                </div>
                                            )}
                                            {notification.context.task_description && (
                                                <div>
                                                    <div className="text-xs font-medium text-muted-foreground">Description</div>
                                                    <div className="text-sm text-foreground">{notification.context.task_description}</div>
                                                </div>
                                            )}
                                            <div className="flex flex-wrap gap-4">
                                                {notification.context.task_priority && (
                                                    <div className="flex items-center gap-2">
                                                        <Flag className="size-3.5 text-muted-foreground" />
                                                        <span className="text-xs text-muted-foreground">Priority:</span>
                                                        <Badge variant="outline" className="text-xs capitalize">
                                                            {notification.context.task_priority}
                                                        </Badge>
                                                    </div>
                                                )}
                                                {notification.context.task_due_date && (
                                                    <div className="flex items-center gap-2">
                                                        <Calendar className="size-3.5 text-muted-foreground" />
                                                        <span className="text-xs text-muted-foreground">Due:</span>
                                                        <span className="text-xs text-foreground">
                                                            {new Date(notification.context.task_due_date).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                )}
                                                {notification.context.task_assignee && (
                                                    <div className="flex items-center gap-2">
                                                        <User className="size-3.5 text-muted-foreground" />
                                                        <span className="text-xs text-muted-foreground">Assignee:</span>
                                                        <span className="text-xs text-foreground">{notification.context.task_assignee}</span>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}

                        {notification.action_url && (
                            <div className="mt-6">
                                <Button asChild variant="secondary" className="gap-2">
                                    <a href={notification.action_url} target="_blank" rel="noopener noreferrer">
                                        <ExternalLink className="size-4" />
                                        Open related link
                                    </a>
                                </Button>
                            </div>
                        )}
                    </CardContent>

                    <Separator />

                    <CardFooter className="flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        {/* Timestamps */}
                        <div className="flex w-full justify-between gap-x-6 gap-y-2 text-sm text-muted-foreground">
                            <div className="flex items-center gap-1.5">
                                <Calendar className="size-4" />
                                <span>Sent: {formatDate(notification.created_at)}</span>
                                <span className="text-muted-foreground/50">at {formatTime(notification.created_at)}</span>
                            </div>
                            {notification.read_at && (
                                <div className="flex items-center gap-1.5">
                                    <CheckCircle className="size-4" />
                                    <span>Read: {formatDate(notification.read_at)}</span>
                                    <span className="text-muted-foreground/50">at {formatTime(notification.read_at)}</span>
                                </div>
                            )}
                        </div>

                        {/* Action Buttons - Mobile */}
                        <div className="flex w-full gap-2 sm:hidden">
                            {isRead ? (
                                <Button variant="outline" size="sm" onClick={markAsUnread} className="flex-1 gap-1.5">
                                    <Mail className="size-4" />
                                    Mark unread
                                </Button>
                            ) : (
                                <Button variant="outline" size="sm" onClick={markAsRead} className="flex-1 gap-1.5">
                                    <CheckCircle className="size-4" />
                                    Mark read
                                </Button>
                            )}
                            <Button variant="destructive" size="sm" onClick={deleteNotification} className="flex-1 gap-1.5">
                                <Trash2 className="size-4" />
                                Delete
                            </Button>
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
