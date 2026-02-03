import AppLogo from '@/shared/components/app-logo';
import AppLogoIcon from '@/shared/components/app-logo-icon';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/shared/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/shared/components/ui/sidebar';
import { useIsMobile } from '@/shared/hooks/use-mobile';
import type { ClientSummary, SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Building2, Check, ChevronsUpDown, Users } from 'lucide-react';
import { useTranslation } from 'react-i18next';

// Routes
import ClientSwitchController from '@/actions/App/Http/Controllers/ClientSwitchController';
import { dashboard as ApplicationDashboard } from '@/routes';

export function SidebarHeader() {
    const page = usePage<SharedData>();
    const { modules, currentClient, userClients, name, branding } = page.props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();
    const { t } = useTranslation();

    const handleClientSwitch = (client: ClientSummary) => {
        router.post(ClientSwitchController(client.id).url);
    };

    const isCollapsed = state === 'collapsed';

    // Show simple logo when clients module is disabled or user has no clients
    if (!modules.clients.enabled || userClients.length === 0) {
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

    // Show dropdown when clients module is enabled and user has clients
    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="group data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                                <Building2 className="size-4" />
                            </div>
                            <div className="ml-1 grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-semibold">{currentClient?.name ?? t('sidebar.select_client')}</span>
                                <span className="truncate text-xs text-muted-foreground">
                                    {userClients.length > 1
                                        ? t('sidebar.clients_count', { count: userClients.length })
                                        : t('sidebar.client_workspace')}
                                </span>
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? 'bottom' : state === 'collapsed' ? 'right' : 'bottom'}
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className="text-xs text-muted-foreground">{t('sidebar.client_workspace')}</DropdownMenuLabel>
                        {userClients.map((client) => (
                            <DropdownMenuItem key={client.id} onClick={() => handleClientSwitch(client)} className="cursor-pointer gap-2 p-2">
                                <div className="flex size-6 items-center justify-center rounded-sm border">
                                    <Building2 className="size-4 shrink-0" />
                                </div>
                                <span className="flex-1 truncate">{client.name}</span>
                                {currentClient?.id === client.id && <Check className="ml-auto size-4" />}
                            </DropdownMenuItem>
                        ))}
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild className="cursor-pointer gap-2 p-2">
                            <Link href="/client/users" prefetch>
                                <div className="flex size-6 items-center justify-center rounded-sm border bg-background">
                                    <Users className="size-4" />
                                </div>
                                {t('sidebar.manage_users')}
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
