import { leave as leaveImpersonation } from '@/routes/impersonate';
import { AppContent } from '@/shared/components/app-content';
import { AppShell } from '@/shared/components/app-shell';
import { AppSidebar } from '@/shared/components/app-sidebar';
import { AppSidebarHeader } from '@/shared/components/app-sidebar-header';
import { SharedData, type BreadcrumbItem } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { AlertCircle, Loader2, LogOut } from 'lucide-react';
import { type PropsWithChildren } from 'react';
import { useTranslation } from 'react-i18next';

function ImpersonationBanner() {
    const { t } = useTranslation();
    const { auth } = usePage<SharedData>().props;
    const { post, processing } = useForm();

    const handleLeaveImpersonation = () => {
        post(leaveImpersonation().url);
    };

    return (
        <div className="sticky top-0 z-50 flex h-8 items-center justify-center gap-2 bg-amber-500 px-4 py-2 text-sm font-medium text-amber-950 dark:bg-amber-600 dark:text-amber-50">
            <AlertCircle className="size-4" />
            <span>
                {t('navigation.labels.currently_viewing_as')} <strong>{auth.user.name}</strong>
            </span>

            <button
                type="button"
                onClick={handleLeaveImpersonation}
                disabled={processing}
                className="ml-auto flex items-center gap-2 rounded border border-amber-950/30 bg-transparent px-2 py-1 text-xs font-semibold text-amber-950 transition-colors hover:bg-amber-600 hover:text-amber-50 disabled:opacity-50 dark:border-amber-50/30 dark:text-amber-50 dark:hover:bg-amber-950/40 dark:hover:text-amber-50"
            >
                {processing ? <Loader2 className="size-4 animate-spin" /> : <LogOut className="size-4" />}
                {processing
                    ? t('navigation.actions.leaving_impersonation', 'Leaving impersonation')
                    : t('navigation.actions.leave_impersonation', 'Leave impersonation')}
            </button>
        </div>
    );
}

export default function AppSidebarLayout({ children, breadcrumbs = [] }: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
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
