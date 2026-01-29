import { Icon } from '@/shared/components/icon';
import { NavFooter } from '@/shared/components/nav-footer';
import { NavMain } from '@/shared/components/nav-main';
import { NavUser } from '@/shared/components/nav-user';
import { SidebarHeader } from '@/shared/components/sidebar-header';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader as SidebarHeaderContainer,
    SidebarMenu,
    SidebarMenuItem,
} from '@/shared/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { BellIcon, LayoutGrid, List, LogOut, Shield } from 'lucide-react'
import { useTranslation } from 'react-i18next';

// Routes
import { dashboard as ApplicationDashboard, } from '@/routes';
import { leave as leaveImpersonation } from '@/routes/impersonate';
import tasks from '@/routes/tasks';
import { Button } from './ui/button';
import notifications from '@/routes/notifications';
import { AppSearchBar } from './app-search-bar';
import { UnreadNotificationIcon } from './custom-icons';

export function AppSidebar() {
    const page = usePage<SharedData>();
    const { permissions, isImpersonating, modules } = page.props;
    const { t } = useTranslation();

    const mainNavItems: NavItem[] = [
        {
            title: t('navigation.labels.dashboard'),
            href: ApplicationDashboard(),
            icon: LayoutGrid,
        },

        ...(modules.tasks.enabled
            ? [
                  {
                      title: t('navigation.labels.tasks'),
                      href: tasks.index(),
                      icon: List,
                  },
              ]
            : []),
    ];

    const footerNavItems: NavItem[] = [
        ...(permissions.canAccessControlPanel
            ? [
                  {
                      title: t('navigation.labels.admin_panel'),
                      href: '/admin',
                      icon: Shield,
                  },
              ]
            : []),

        {
            title: t('navigation.labels.notifications'),
            href: notifications.index(),
            icon: (page.props?.unreadNotificationsCount ?? 0) > 0 ? UnreadNotificationIcon : BellIcon,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeaderContainer>
                <SidebarHeader />
            </SidebarHeaderContainer>

            <SidebarContent>
                <AppSearchBar mainNavItems={mainNavItems} footerNavItems={footerNavItems} />

                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                {isImpersonating && (
                    <SidebarGroup className="mt-auto group-data-[collapsible=icon]:p-0">
                        <SidebarGroupContent>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <Button onClick={() => router.post(leaveImpersonation().url)} className="w-full cursor-pointer" variant="ghost">
                                        <Icon iconNode={LogOut} className="h-5 w-5" />
                                        <span>{t('navigation.labels.leave_impersonation')}</span>
                                    </Button>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroupContent>
                    </SidebarGroup>
                )}

                {footerNavItems.length > 0 && <NavFooter items={footerNavItems} className="mt-auto" />}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
