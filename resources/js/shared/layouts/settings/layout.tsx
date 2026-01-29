import Heading from '@/shared/components/heading';
import { Button } from '@/shared/components/ui/button';
import { Separator } from '@/shared/components/ui/separator';
import { cn, isSameUrl, resolveUrl } from '@/shared/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { preferences as notificationsPreferences } from '@/routes/notifications';
import { edit } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';
import { useTranslation } from 'react-i18next';

export default function SettingsLayout({ children }: PropsWithChildren) {
    const page = usePage<SharedData>();
    const { permissions } = page.props;
    const { t } = useTranslation();

    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    const sidebarNavItems: NavItem[] = [
        {
            title: t('settings.sidebar.items.profile'),
            href: edit(),
            icon: null,
            group: t('settings.sidebar.groups.account'),
        },
        {
            title: t('settings.sidebar.items.password'),
            href: editPassword(),
            icon: null,
            group: t('settings.sidebar.groups.security'),
        },
        {
            title: t('settings.sidebar.items.two_factor'),
            href: show(),
            icon: null,
            group: t('settings.sidebar.groups.security'),
        },
        {
            title: t('settings.sidebar.items.notifications'),
            href: notificationsPreferences(),
            icon: null,
            group: t('settings.sidebar.groups.preferences'),
        },
        ...(permissions.canManageApiTokens
            ? [
                  {
                      title: t('settings.sidebar.items.api_tokens'),
                      href: '/settings/api-tokens',
                      icon: null,
                      group: t('settings.sidebar.groups.security'),
                  },
              ]
            : []),
        {
            title: t('settings.sidebar.items.appearance'),
            href: editAppearance(),
            icon: null,
            group: t('settings.sidebar.groups.preferences'),
        },
    ];

    const groupedSidebarNavItems = sidebarNavItems.reduce<Record<string, NavItem[]>>((groups, item) => {
        const groupName = item.group ?? t('settings.sidebar.groups.general');

        if (!groups[groupName]) {
            groups[groupName] = [];
        }

        groups[groupName].push(item);

        return groups;
    }, {});

    return (
        <div className="px-4 py-6">
            <Heading title={t('settings.sidebar.heading')} description={t('settings.sidebar.description')} />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-8 space-x-0">
                        {Object.entries(groupedSidebarNavItems).map(([groupName, items]) => (
                            <div key={groupName} className="flex flex-col space-y-1">
                                <div className="px-1 text-xs font-semibold tracking-wide text-muted-foreground/60 uppercase">{groupName}</div>

                                {items.map((item, index) => (
                                    <Button
                                        key={`${resolveUrl(item.href)}-${index}`}
                                        size="sm"
                                        variant="ghost"
                                        asChild
                                        className={cn('w-full justify-start', {
                                            'bg-muted': isSameUrl(currentPath, item.href),
                                        })}
                                    >
                                        <Link href={item.href}>
                                            {item.icon && <item.icon className="h-4 w-4" />}
                                            {item.title}
                                        </Link>
                                    </Button>
                                ))}
                            </div>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-4xl">
                    <section className="max-w-xl space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
