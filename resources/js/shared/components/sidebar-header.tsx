import { dashboard as ApplicationDashboard } from '@/routes';
import AppLogo from '@/shared/components/app-logo';
import AppLogoIcon from '@/shared/components/app-logo-icon';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/shared/components/ui/sidebar';
import { Link } from '@inertiajs/react';

export function SidebarHeader() {
    const { state } = useSidebar();
    const isCollapsed = state === 'collapsed';

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    <Link href={ApplicationDashboard()} prefetch>
                        {isCollapsed ? <AppLogoIcon className="mx-auto size-6 fill-current text-white dark:text-black" /> : <AppLogo />}
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
