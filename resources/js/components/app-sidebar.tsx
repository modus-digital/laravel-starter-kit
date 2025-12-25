import { Icon } from '@/components/icon';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { LayoutGrid, List, LogOut, Shield } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLogo from './app-logo';

// Routes
import { dashboard as ApplicationDashboard } from '@/routes';
import { dashboard as ControlPanelDashboard } from '@/routes/filament/control/pages';
import { leave as leaveImpersonation } from '@/routes/impersonate';
import tasks from '@/routes/tasks';
import { Button } from './ui/button';

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
        // If the user is not impersonating, show the admin panel button
        ...(permissions.canAccessControlPanel
            ? [
                  {
                      title: t('navigation.labels.admin_panel'),
                      href: ControlPanelDashboard(),
                      icon: Shield,
                  },
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={ApplicationDashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
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
