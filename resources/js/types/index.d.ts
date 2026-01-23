import { ModulesConfig } from '@/types/modules';
import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';
import { ComponentType } from 'react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

// Icon can be a LucideIcon or a custom component with className prop
export type NavIconComponent = LucideIcon | ComponentType<{ className?: string }>;

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: NavIconComponent | null;
    isActive?: boolean;
    group?: string;
}

export interface ClientSummary {
    id: string;
    name: string;
}

export interface SharedData {
    name: string;
    auth: Auth;
    unreadNotificationsCount?: number;
    sidebarOpen: boolean;
    isImpersonating: boolean;
    permissions: {
        canAccessControlPanel: boolean;
        canManageApiTokens: boolean;
        [permission: string]: boolean;
    };
    modules: ModulesConfig;
    branding: {
        logo: string;
        primaryColor: string;
        secondaryColor: string;
        font: string;
        logoAspectRatio?: '1:1' | '16:9';
    };
    currentClient?: ClientSummary | null;
    userClients: ClientSummary[];
    data?: Record<string, unknown>;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    avatar_url?: string | null;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface SearchResult {
    id: string;
    type: string;      // e.g., 'User', 'Client'
    label: string;     // e.g., 'John Doe'
    subtitle?: string; // e.g., 'john@example.com'
    icon?: string;
    url: string;       // The show route URL
}
