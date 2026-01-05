import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuShortcut } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { index as NotificationsIndex } from '@/routes/notifications';
import { edit } from '@/routes/profile';
import { type User } from '@/types';
import { Link, router } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { UnreadNotificationIcon } from './custom-icons';

interface UserMenuContentProps {
    user: User;
    unreadNotificationsCount?: number;
}

export function UserMenuContent({ user, unreadNotificationsCount = 0 }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();
    const { t } = useTranslation();

    const hasUnreadNotifications = unreadNotificationsCount > 0;
    const unreadNotificationsLabel = unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount.toString();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} hasUnreadNotifications={hasUnreadNotifications} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link className="flex w-full items-center" href={NotificationsIndex().url} as="button" prefetch onClick={cleanup}>
                        <UnreadNotificationIcon className="mr-2" />
                        {t('navigation.labels.notifications')}
                        {hasUnreadNotifications && <DropdownMenuShortcut>{unreadNotificationsLabel}</DropdownMenuShortcut>}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link className="block w-full" href={edit()} as="button" prefetch onClick={cleanup}>
                        <Settings className="mr-2" />
                        {t('common.actions.settings')}
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link className="block w-full" href={logout()} as="button" onClick={handleLogout} data-test="logout-button">
                    <LogOut className="mr-2" />
                    {t('common.actions.logout')}
                </Link>
            </DropdownMenuItem>
        </>
    );
}
