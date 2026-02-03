import { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import AppLogoIcon from './app-logo-icon';

/** Branding can come from shared data (camelCase) or branding page (snake_case) */
function brandingValues(branding: Record<string, unknown>) {
    return {
        logoLight: (branding.logoLight ?? branding.logo_light) as string | null | undefined,
        logoDark: (branding.logoDark ?? branding.logo_dark) as string | null | undefined,
        emblemLight: (branding.emblemLight ?? branding.emblem_light) as string | null | undefined,
        emblemDark: (branding.emblemDark ?? branding.emblem_dark) as string | null | undefined,
    };
}

export default function AppLogo() {
    const page = usePage<SharedData>();
    const { branding, name } = page.props;
    const { logoLight, logoDark, emblemLight, emblemDark } = brandingValues(branding as Record<string, unknown>);
    const hasLogo = logoLight || logoDark;
    const hasEmblem = emblemLight || emblemDark;
    const shouldShowAppName = !hasLogo && !hasEmblem;

    if (hasLogo) {
        return (
            <>
                <div className="flex h-8 shrink-0 items-center">
                    {logoLight && <img src={logoLight} alt="App Logo" className="h-8 w-auto max-w-full object-contain dark:hidden" />}
                    {logoDark && <img src={logoDark} alt="App Logo" className="hidden h-8 w-auto max-w-full object-contain dark:block" />}
                    {logoLight && !logoDark && (
                        <img src={logoLight} alt="App Logo" className="hidden h-8 w-auto max-w-full object-contain dark:block" />
                    )}
                    {logoDark && !logoLight && <img src={logoDark} alt="App Logo" className="h-8 w-auto max-w-full object-contain dark:hidden" />}
                </div>
            </>
        );
    }

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            </div>
            {shouldShowAppName && (
                <div className="ml-1 grid flex-1 text-left text-sm">
                    <span className="mb-0.5 truncate leading-tight font-semibold">{name}</span>
                </div>
            )}
        </>
    );
}
