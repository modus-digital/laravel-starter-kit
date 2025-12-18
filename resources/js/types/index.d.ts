import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

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

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
    group?: string;
}

export interface SharedData {
    name: string;
    auth: Auth;
    unreadNotificationsCount?: number;
    sidebarOpen: boolean;
    canAccessControlPanel: boolean;
    canManageApiTokens: boolean;
    isImpersonating: boolean;
    permissions: {
        canAccessControlPanel: boolean;
        canManageApiTokens: boolean;
        [permission: string]: boolean;
    };
    branding: {
        logo: string;
        primaryColor: string;
        secondaryColor: string;
        font: string;
    };
    data?: Record<string, unknown>;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface SocialiteProvider {
    id: string;
    name: string;
}
