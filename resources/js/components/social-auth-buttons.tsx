import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { redirect } from '@/routes/oauth';
import { SocialiteProvider } from '@/types';

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
    if (providers.length === 0) {
        return null;
    }

    const isMultiple = providers.length > 1;

    return (
        <TooltipProvider>
            <div className="flex gap-2 w-full mb-4">
                {providers.map((provider) => {
                    const icon = providerIcons[provider.name] ?? MicrosoftIcon;
                    const providerName = provider.name.charAt(0).toUpperCase() + provider.name.slice(1);

                    const button = (
                        <Button
                            variant="outline"
                            className={cn(isMultiple ? 'flex-1 px-3' : 'w-full')}
                            asChild
                        >
                            <a href={redirect.url({ provider: provider.name })}>
                                <img
                                    src={icon}
                                    alt={provider.name}
                                    className={cn(
                                        isMultiple ? 'w-6 h-6' : 'w-4 h-4',
                                        provider.name === 'github' && 'dark:invert'
                                    )}
                                />
                                {!isMultiple && (
                                    <span className="ml-2">
                                        Login with {providerName}
                                    </span>
                                )}
                            </a>
                        </Button>
                    );

                    if (!isMultiple) {
                        return <div key={provider.id} className="w-full">{button}</div>;
                    }

                    return (
                        <Tooltip key={provider.id}>
                            <TooltipTrigger asChild>
                                {button}
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Login with {providerName}</p>
                            </TooltipContent>
                        </Tooltip>
                    );
                })}
            </div>
        </TooltipProvider>
    );
}

