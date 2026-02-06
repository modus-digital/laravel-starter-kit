import { ClientSidebarGroup } from '@/shared/components/client-sidebar-group';
import { NavFooter } from '@/shared/components/nav-footer';
import { NavMain } from '@/shared/components/nav-main';
import { NavUser } from '@/shared/components/nav-user';
import { SidebarHeader } from '@/shared/components/sidebar-header';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader as SidebarHeaderContainer } from '@/shared/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { BellIcon, LayoutGrid, List, Shield } from 'lucide-react';
import { useTranslation } from 'react-i18next';

// Routes
import { dashboard as ApplicationDashboard } from '@/routes';
import notifications from '@/routes/notifications';
import tasks from '@/routes/tasks';
import { AppSearchBar } from './app-search-bar';
import { UnreadNotificationIcon } from './custom-icons';

export function AppSidebar() {
    const page = usePage<SharedData>();
    const { permissions, modules } = page.props;
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

                <ClientSidebarGroup />
            </SidebarContent>

            <SidebarFooter>
                {footerNavItems.length > 0 && <NavFooter items={footerNavItems} className="mt-auto" />}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
