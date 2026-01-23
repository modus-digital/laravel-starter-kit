import { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    const page = usePage<SharedData>();
    const { branding, name } = page.props;
    const isWideLogo = branding.logo && branding.logoAspectRatio === '16:9';
    const shouldShowAppName = !branding.logo || branding.logoAspectRatio !== '16:9';

    return (
        <>
            {isWideLogo ? (
                // Wide logo: no background, full width
                <AppLogoIcon className="h-8 w-auto max-w-full object-contain" />
            ) : (
                // Square logo or default: with background container
                <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                    <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                </div>
            )}
            {shouldShowAppName && (
                <div className="ml-1 grid flex-1 text-left text-sm">
                    <span className="mb-0.5 truncate leading-tight font-semibold">{name}</span>
                </div>
            )}
        </>
    );
}
