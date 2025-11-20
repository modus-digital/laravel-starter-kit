import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { SharedData, type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertCircle, LogOut } from 'lucide-react';
import { type PropsWithChildren } from 'react';
import { useTranslation } from 'react-i18next';
import { leave as leaveImpersonation } from '@/routes/impersonate';

function ImpersonationBanner() {
    const { t } = useTranslation();
    const { auth } = usePage<SharedData>().props;

    return (
        <div className="sticky top-0 h-8 z-50 flex items-center justify-center gap-2 bg-amber-500 px-4 py-2 text-sm font-medium text-amber-950 dark:bg-amber-600 dark:text-amber-50">
            <AlertCircle className="size-4" />
            <span>
                {t('navigation.labels.currently_viewing_as')} {' '}
                <strong>{auth.user.name}</strong>
            </span>
        </div>
    );
}

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {   
    const { isImpersonating } = usePage<SharedData>().props;
    
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                {isImpersonating && <ImpersonationBanner />}
                
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
