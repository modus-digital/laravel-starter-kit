import { Icon } from '@/shared/components/icon';
import { NavFooter } from '@/shared/components/nav-footer';
import { NavMain } from '@/shared/components/nav-main';
import { NavUser } from '@/shared/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/shared/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Activity, Languages, LayoutDashboard, LayoutGrid, LogOut, Mail, Palette, Plug, Shield, Users } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLogo from './app-logo';
import { Button } from './ui/button';
import { AppSearchBar } from './app-search-bar';

// Routes
import { dashboard as ApplicationDashboard } from '@/routes';
import { leave as leaveImpersonation } from '@/routes/impersonate';

export function AppAdminSidebar() {
    const page = usePage<SharedData>();
    const { isImpersonating, modules } = page.props;
    const { t } = useTranslation();

    const adminNavItems: NavItem[] = [
        {
            title: t('admin.sidebar.dashboard'),
            href: '/admin',
            icon: LayoutDashboard,
            group: t('admin.sidebar.groups.overview'),
        },
        {
            title: t('admin.sidebar.users'),
            href: '/admin/users',
            icon: Users,
            group: t('admin.sidebar.groups.management'),
        },
        {
            title: t('admin.sidebar.roles'),
            href: '/admin/roles',
            icon: Shield,
            group: t('admin.sidebar.groups.management'),
        },
        ...(modules?.clients?.enabled
            ? ([
                  {
                      title: t('admin.sidebar.clients') as string,
                      href: '/admin/clients',
                      icon: Users,
                      group: t('admin.sidebar.groups.management') as string,
                  },
              ] as NavItem[])
            : []),
        {
            title: t('admin.sidebar.branding'),
            href: '/admin/branding',
            icon: Palette,
            group: t('admin.sidebar.groups.system'),
        },
        {
            title: t('admin.sidebar.activity_logs'),
            href: '/admin/activities',
            icon: Activity,
            group: t('admin.sidebar.groups.system'),
        },
        {
            title: t('admin.sidebar.translations'),
            href: '/admin/translations',
            icon: Languages,
            group: t('admin.sidebar.groups.system'),
        },
        {
            title: t('admin.sidebar.integrations'),
            href: '/admin/integrations',
            icon: Plug,
            group: t('admin.sidebar.groups.integrations'),
        },
        {
            title: t('admin.sidebar.mailgun_analytics'),
            href: '/admin/mailgun',
            icon: Mail,
            group: t('admin.sidebar.groups.integrations'),
        },
    ];

    // Group admin nav items
    const groupedAdminNavItems = adminNavItems.reduce<Record<string, NavItem[]>>((groups, item) => {
        const groupName = item.group ?? t('admin.sidebar.groups.general');

        if (!groups[groupName]) {
            groups[groupName] = [];
        }

        groups[groupName].push(item);

        return groups;
    }, {});

    const footerNavItems: NavItem[] = [
        {
            title: t('navigation.labels.dashboard'),
            href: ApplicationDashboard(),
            icon: LayoutGrid,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/admin" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <AppSearchBar mainNavItems={adminNavItems} footerNavItems={footerNavItems} />
                
                {Object.entries(groupedAdminNavItems).map(([groupName, items]) => (
                    <SidebarGroup key={groupName}>
                        <SidebarGroupLabel>{groupName}</SidebarGroupLabel>
                        <SidebarGroupContent>
                            <NavMain items={items} withoutGroupLabel />
                        </SidebarGroupContent>
                    </SidebarGroup>
                ))}
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
