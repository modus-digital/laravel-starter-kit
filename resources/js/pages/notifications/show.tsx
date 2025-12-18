import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import type { Notification as NotificationType } from '@/types/models';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Bell,
    Calendar,
    CheckCircle,
    Clock,
    ExternalLink,
    Mail,
    Trash2,
} from 'lucide-react';

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

function formatRelativeTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) {
        return 'Just now';
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
    }

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    }

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) {
        return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
    }

    return formatDate(dateString);
}

export default function Notification({ notification }: NotificationProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Notifications',
            href: '/notifications',
        },
        {
            title: notification.title,
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
            <Head title={notification.title} />

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
                                        isRead
                                            ? 'bg-muted text-muted-foreground'
                                            : 'bg-primary/10 text-primary'
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
                                        {notification.deleted_at && (
                                            <Badge variant="destructive">Deleted</Badge>
                                        )}
                                    </div>
                                    <CardTitle className="text-xl sm:text-2xl">
                                        {notification.title}
                                    </CardTitle>
                                    <CardDescription className="flex items-center gap-1.5">
                                        <Clock className="size-3.5" />
                                        {formatRelativeTime(notification.created_at)}
                                    </CardDescription>
                                </div>
                            </div>

                            {/* Action Buttons - Desktop */}
                            <div className="hidden shrink-0 gap-2 sm:flex">
                                {isRead ? (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={markAsUnread}
                                        className="gap-1.5"
                                    >
                                        <Mail className="size-4" />
                                        Mark unread
                                    </Button>
                                ) : (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={markAsRead}
                                        className="gap-1.5"
                                    >
                                        <CheckCircle className="size-4" />
                                        Mark read
                                    </Button>
                                )}
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    onClick={deleteNotification}
                                    className="gap-1.5"
                                >
                                    <Trash2 className="size-4" />
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </CardHeader>

                    <Separator />

                    <CardContent className="py-6">
                        <div className="prose prose-sm dark:prose-invert max-w-none">
                            <p className="whitespace-pre-wrap text-foreground leading-relaxed">
                                {notification.body}
                            </p>
                        </div>

                        {notification.action_url && (
                            <div className="mt-6">
                                <Button asChild variant="secondary" className="gap-2">
                                    <a
                                        href={notification.action_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
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
                                <span className="text-muted-foreground/50">
                                    at {formatTime(notification.created_at)}
                                </span>
                            </div>
                            {notification.read_at && (
                                <div className="flex items-center gap-1.5">
                                    <CheckCircle className="size-4" />
                                    <span>Read: {formatDate(notification.read_at)}</span>
                                    <span className="text-muted-foreground/50">
                                        at {formatTime(notification.read_at)}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Action Buttons - Mobile */}
                        <div className="flex w-full gap-2 sm:hidden">
                            {isRead ? (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={markAsUnread}
                                    className="flex-1 gap-1.5"
                                >
                                    <Mail className="size-4" />
                                    Mark unread
                                </Button>
                            ) : (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={markAsRead}
                                    className="flex-1 gap-1.5"
                                >
                                    <CheckCircle className="size-4" />
                                    Mark read
                                </Button>
                            )}
                            <Button
                                variant="destructive"
                                size="sm"
                                onClick={deleteNotification}
                                className="flex-1 gap-1.5"
                            >
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
