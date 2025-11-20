import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { LogOut, LayoutGrid, Shield } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLogo from './app-logo';

// Routes
import { leave as leaveImpersonation } from '@/routes/impersonate';
import { dashboard as ApplicationDashboard } from '@/routes';
import { dashboard as ControlPanelDashboard } from '@/routes/filament/control/pages';

export function AppSidebar() {
    const { canAccessControlPanel, isImpersonating } = usePage<SharedData>().props;
    const { t } = useTranslation();

    const mainNavItems: NavItem[] = [
        {
            title: t('navigation.labels.dashboard'),
            href: ApplicationDashboard(),
            icon: LayoutGrid,
        },
    ];

    const footerNavItems: NavItem[] = [
        // If the user is impersonating, show the leave impersonation button
        ...(isImpersonating ? [
            {
                title: t('navigation.labels.leave_impersonation'),
                href: leaveImpersonation(),
                icon: LogOut,
            },
        ] : []),

        // If the user is not impersonating, show the admin panel button
        ...(canAccessControlPanel ? [
            {
                title: t('navigation.labels.admin_panel'),
                href: ControlPanelDashboard(),
                icon: Shield,
            },
        ] : []),
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
                <NavFooter
                    items={canAccessControlPanel ? footerNavItems : []}
                    className="mt-auto"
                />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
