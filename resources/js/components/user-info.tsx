import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';

export function UserInfo({
    user,
    showEmail = false,
    hasUnreadNotifications = false,
}: {
    user: User;
    showEmail?: boolean;
    hasUnreadNotifications?: boolean;
}) {
    const getInitials = useInitials();

    return (
        <>
            <div className="relative">
                <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                    <AvatarImage src={user.avatar} alt={user.name} />
                    <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                        {getInitials(user.name)}
                    </AvatarFallback>
                </Avatar>
                {hasUnreadNotifications && (
                    <span className="absolute -top-0.5 -right-0.5 inline-block h-2 w-2 rounded-full bg-destructive ring-2 ring-background" />
                )}
            </div>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                {showEmail && (
                    <span className="truncate text-xs text-muted-foreground">
                        {user.email}
                    </span>
                )}
            </div>
        </>
    );
}
