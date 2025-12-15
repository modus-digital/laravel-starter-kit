import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import type { Notification as NotificationType } from '@/types/models';
import { Head, router } from '@inertiajs/react';


type NotificationProps = {
    notification: NotificationType;
};

export default function Notification({ notification }: NotificationProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Notifications',
            href: '/notifications',
        },
        {
            title: notification.title,
            href: `/notifications/${notification.id}/show`,
        },
    ]

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={notification.title} />
            <div className="mx-auto my-8 flex w-full max-w-7xl flex-col gap-4">
                <div className="flex w-full items-center justify-between">
                    <h1 className="text-xl font-semibold">{notification.title}</h1>
                </div>
                <div className="flex w-full items-center justify-between">
                    <p className="text-sm text-muted-foreground">{notification.body}</p>
                </div>
                <div className="flex w-full items-center justify-between">
                    <p className="text-sm text-muted-foreground">{notification.created_at}</p>
                </div>
                <div className="flex w-full items-center justify-between">
                    <p className="text-sm text-muted-foreground">{notification.updated_at}</p>
                </div>
                <div className="flex w-full items-center justify-between">
                    <p className="text-sm text-muted-foreground">{notification.deleted_at}</p>
                </div>
            </div>
        </AppLayout>
    );
}