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
import { dashboard } from '@/routes';
import control from '@/routes/filament/control';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, Shield } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { canAccessControlPanel } = usePage<SharedData>().props;
    const { t } = useTranslation();

    const mainNavItems: NavItem[] = [
        {
            title: t('navigation.labels.dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: t('navigation.labels.admin_panel'),
            href: control.pages.dashboard(),
            icon: Shield,
        },
    ];
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
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
