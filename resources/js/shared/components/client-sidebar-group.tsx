import AppLogoIcon from '@/shared/components/app-logo-icon';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/shared/components/ui/dropdown-menu';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/shared/components/ui/sidebar';
import { useIsMobile } from '@/shared/hooks/use-mobile';
import type { ClientSummary, SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Settings, Users } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import ClientSwitchController from '@/actions/App/Http/Controllers/ClientSwitchController';

export function ClientSidebarGroup() {
    const page = usePage<SharedData>();
    const { modules, currentClient, userClients } = page.props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();
    const { t } = useTranslation();

    const handleClientSwitch = (client: ClientSummary) => {
        router.post(ClientSwitchController(client.id).url);
    };

    if (!modules.clients.enabled || userClients.length === 0 || !currentClient) {
        return null;
    }

    const hasMultipleClients = userClients.length > 1;

    return (
        <SidebarGroup>
            <SidebarGroupLabel asChild>
                {hasMultipleClients ? (
                    <DropdownMenu>
                        <DropdownMenuTrigger className="flex w-full items-center gap-2 text-base font-semibold hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <span className="flex-1 truncate">{currentClient.name}</span>
                            <ChevronsUpDown className="size-4" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            className="min-w-56 rounded-lg"
                            align="start"
                            side={isMobile ? 'bottom' : state === 'collapsed' ? 'right' : 'bottom'}
                            sideOffset={4}
                        >
                            <DropdownMenuLabel className="text-xs text-muted-foreground">{t('sidebar.client_workspace')}</DropdownMenuLabel>
                            {userClients.map((client) => (
                                <DropdownMenuItem key={client.id} onClick={() => handleClientSwitch(client)} className="cursor-pointer gap-2 p-2">
                                    <div className="flex size-6 items-center justify-center rounded-sm border">
                                        <AppLogoIcon className="size-4 shrink-0" />
                                    </div>
                                    <span className="flex-1 truncate">{client.name}</span>
                                    {currentClient.id === client.id && <Check className="ml-auto size-4" />}
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                ) : (
                    <div className="text-base font-semibold">{currentClient.name}</div>
                )}
            </SidebarGroupLabel>
            <SidebarGroupContent>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild tooltip={{ children: t('sidebar.manage_users', 'Manage Users') }}>
                            <Link href="/manage/users" prefetch>
                                <Users className="size-4" />
                                <span>{t('sidebar.manage_users', 'Manage Users')}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild tooltip={{ children: t('sidebar.client_settings', 'Client Settings') }}>
                            <Link href="/manage/settings" prefetch>
                                <Settings className="size-4" />
                                <span>{t('sidebar.client_settings', 'Client Settings')}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}
