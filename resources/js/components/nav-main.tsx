import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { resolveUrl } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export function NavMain({ items = [], withoutGroupLabel = false }: { items: NavItem[]; withoutGroupLabel?: boolean }) {
    const page = usePage();
    const { t } = useTranslation();
    return (
        <SidebarGroup className="px-2 py-0">
            {!withoutGroupLabel && <SidebarGroupLabel>{t('navigation.groups.platform', 'Platform')}</SidebarGroupLabel>}
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={page.url.startsWith(resolveUrl(item.href))} tooltip={{ children: item.title }}>
                            <Link href={item.href} prefetch>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
