import { login } from '@/routes';
import { Alert, AlertDescription, AlertTitle } from '@/shared/components/ui/alert';
import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { PlaceholderPattern } from '@/shared/components/ui/placeholder-pattern';
import { Separator } from '@/shared/components/ui/separator';
import { cn } from '@/shared/lib/utils';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Home, LogIn, RefreshCcw, TriangleAlert } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type ErrorPageProps = {
    status: number;
};

type ErrorMeta = {
    title: string;
    message: string;
    hint?: string;
};

type Translate = (key: string, defaultValue: string) => string;

function getErrorMeta(status: number, t: Translate): ErrorMeta {
    switch (status) {
        case 401:
            return {
                title: t('errors.401.title', 'Unauthorized'),
                message: t('errors.401.message', 'You need to sign in to access this page.'),
                hint: t('errors.401.hint', 'Try signing in, then come back.'),
            };
        case 402:
            return {
                title: t('errors.402.title', 'Payment Required'),
                message: t('errors.402.message', 'This action requires an active subscription.'),
                hint: t('errors.402.hint', 'If you think this is a mistake, contact your administrator.'),
            };
        case 403:
            return {
                title: t('errors.403.title', 'Forbidden'),
                message: t('errors.403.message', "You don't have permission to access this page."),
                hint: t('errors.403.hint', 'Switch accounts or ask an administrator for access.'),
            };
        case 404:
            return {
                title: t('errors.404.title', 'Not Found'),
                message: t('errors.404.message', "We couldn't find the page you're looking for."),
                hint: t('errors.404.hint', 'Check the URL or head back home.'),
            };
        case 419:
            return {
                title: t('errors.419.title', 'Page Expired'),
                message: t('errors.419.message', 'Your session expired. Please refresh and try again.'),
                hint: t('errors.419.hint', 'Refreshing usually fixes this.'),
            };
        case 429:
            return {
                title: t('errors.429.title', 'Too Many Requests'),
                message: t('errors.429.message', 'You are doing that too often. Please try again shortly.'),
                hint: t('errors.429.hint', 'Wait a moment, then retry.'),
            };
        case 500:
            return {
                title: t('errors.500.title', 'Server Error'),
                message: t('errors.500.message', 'Something went wrong on our end.'),
                hint: t('errors.500.hint', 'Try again in a moment.'),
            };
        case 503:
            return {
                title: t('errors.503.title', 'Service Unavailable'),
                message: t('errors.503.message', 'The service is temporarily unavailable.'),
                hint: t('errors.503.hint', 'Please try again shortly.'),
            };
        case 504:
            return {
                title: t('errors.504.title', 'Gateway Timeout'),
                message: t('errors.504.message', 'The server took too long to respond.'),
                hint: t('errors.504.hint', 'Please try again shortly.'),
            };
        default:
            return {
                title: t('errors.default.title', 'Unexpected Error'),
                message: t('errors.default.message', 'An unexpected error occurred.'),
                hint: t('errors.default.hint', 'Please try again.'),
            };
    }
}

export default function ErrorPage({ status }: ErrorPageProps) {
    const { t } = useTranslation();

    // Narrow `t` to avoid TypeScript's deep instantiation issues with i18next's TFunction overloads.
    const tt: Translate = (key, defaultValue) => t(key, { defaultValue });

    const meta = getErrorMeta(status, tt);
    const isAuthError = status === 401;
    const canRetry = status === 419 || status === 429 || status >= 500;

    const handleGoBack = () => {
        window.history.back();
    };

    const handleRetry = () => {
        window.location.reload();
    };

    return (
        <>
            <Head title={`${status} - ${meta.title}`} />

            <main className="relative flex min-h-dvh items-center justify-center overflow-hidden bg-background px-6 py-12">
                <div className="pointer-events-none absolute inset-0">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/8 dark:stroke-neutral-100/8" />
                    <div className="absolute inset-0 bg-linear-to-b from-background via-background/70 to-background" />
                </div>

                <div className="relative w-full max-w-xl">
                    <div className="relative rounded-[28px] border bg-card shadow-sm">
                        <div className="pointer-events-none absolute inset-0 overflow-hidden rounded-[28px]">
                            <div className="absolute inset-0 opacity-60">
                                <PlaceholderPattern className="size-full stroke-neutral-900/12 dark:stroke-neutral-100/12" />
                            </div>
                            <div className="absolute inset-0 bg-linear-to-b from-background/30 via-background/70 to-background" />
                        </div>

                        <Card className="relative border-0 bg-transparent py-10 shadow-none">
                            <CardHeader className="items-center gap-3 px-8 text-center sm:px-12">
                                <div
                                    className={cn(
                                        'flex size-10 items-center justify-center rounded-full border bg-background/60 text-foreground',
                                        status >= 500 && 'border-destructive/20 bg-destructive/10 text-destructive',
                                    )}
                                    aria-hidden="true"
                                >
                                    <TriangleAlert className="size-5" />
                                </div>

                                <div className="space-y-1">
                                    <div className="text-[72px] leading-none font-semibold tracking-tight sm:text-[88px]">{status}</div>
                                    <CardTitle className="text-xl font-medium sm:text-2xl">{meta.title}</CardTitle>
                                    <CardDescription className="text-sm sm:text-base">{meta.message}</CardDescription>
                                </div>
                            </CardHeader>

                            <CardContent className="space-y-4 px-8 sm:px-12">
                                {meta.hint && (
                                    <Alert>
                                        <TriangleAlert className="size-4" />
                                        <AlertTitle>{tt('errors.next_steps', 'Next steps')}</AlertTitle>
                                        <AlertDescription>{meta.hint}</AlertDescription>
                                    </Alert>
                                )}
                            </CardContent>

                            <Separator className="opacity-70" />

                            <CardFooter className="flex flex-col gap-3 px-8 sm:flex-row sm:items-center sm:justify-center sm:px-12">
                                <Button variant="outline" onClick={handleGoBack} className="w-full sm:w-auto">
                                    <ArrowLeft className="size-4" />
                                    {tt('errors.actions.go_back', 'Go back')}
                                </Button>

                                {canRetry && (
                                    <Button variant="secondary" onClick={handleRetry} className="w-full sm:w-auto">
                                        <RefreshCcw className="size-4" />
                                        {tt('errors.actions.try_again', 'Try again')}
                                    </Button>
                                )}

                                {isAuthError && (
                                    <Button asChild className="w-full sm:w-auto">
                                        <Link href={login()}>
                                            <LogIn className="size-4" />
                                            {tt('errors.actions.sign_in', 'Sign in')}
                                        </Link>
                                    </Button>
                                )}

                                <Button asChild variant={isAuthError ? 'outline' : 'default'} className="w-full sm:w-auto">
                                    <Link href="/">
                                        <Home className="size-4" />
                                        {tt('errors.actions.home', 'Home')}
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>

                    <p className="mt-6 text-center text-sm text-muted-foreground">
                        {tt('errors.footer', 'If this keeps happening, please contact support and include the status code above.')}
                    </p>
                </div>
            </main>
        </>
    );
}
