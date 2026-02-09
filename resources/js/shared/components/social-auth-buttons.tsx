import { redirect } from '@/routes/oauth';
import { Button } from '@/shared/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/shared/components/ui/tooltip';
import { cn } from '@/shared/lib/utils';
import { SocialiteProvider } from '@/types/modules';
import { useTranslation } from 'react-i18next';

import GithubIcon from '@assets/images/github_icon.svg';
import GoogleIcon from '@assets/images/google_icon.svg';
import MicrosoftIcon from '@assets/images/microsoft_icon.svg';

interface SocialAuthButtonsProps {
    providers: SocialiteProvider[];
}

const providerIcons: Record<string, string> = {
    github: GithubIcon,
    google: GoogleIcon,
    microsoft: MicrosoftIcon,
};

export default function SocialAuthButtons({ providers }: SocialAuthButtonsProps) {
    const { t } = useTranslation();
    const isMultiple = providers.length > 1;
    
    if (providers.length === 0) {
        return null;
    }

    return (
        <TooltipProvider>
            <div className="mb-4 flex w-full gap-2">
                {providers.map((provider) => {
                    const icon = providerIcons[provider.name] ?? MicrosoftIcon;
                    const providerName = provider.name.charAt(0).toUpperCase() + provider.name.slice(1);

                    const button = (
                        <Button variant="outline" className={cn(isMultiple ? 'flex-1 px-3' : 'w-full')} asChild>
                            <a href={redirect.url({ provider: provider.name })}>
                                <img
                                    src={icon}
                                    alt={provider.name}
                                    className={cn(isMultiple ? 'h-6 w-6' : 'h-4 w-4', provider.name === 'github' && 'dark:invert')}
                                />
                                {!isMultiple && (
                                    <span className="ml-2">
                                        {t('auth.social.login_with', {
                                            provider: providerName,
                                        })}
                                    </span>
                                )}
                            </a>
                        </Button>
                    );

                    if (!isMultiple) {
                        return (
                            <div key={provider.id} className="w-full">
                                {button}
                            </div>
                        );
                    }

                    return (
                        <Tooltip key={provider.id}>
                            <TooltipTrigger asChild>{button}</TooltipTrigger>
                            <TooltipContent>
                                <p>
                                    {t('auth.social.login_with', {
                                        provider: providerName,
                                    })}
                                </p>
                            </TooltipContent>
                        </Tooltip>
                    );
                })}
            </div>
        </TooltipProvider>
    );
}
