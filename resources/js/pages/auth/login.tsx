import InputError from '@/components/input-error';
import SocialAuthButtons from '@/components/social-auth-buttons';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { SocialiteProvider } from '@/types/modules';
import { Form, Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    authProviders: SocialiteProvider[];
}

export default function Login({
    status,
    canResetPassword,
    canRegister,
    authProviders,
}: LoginProps) {
    const { t } = useTranslation();

    return (
        <AuthLayout
            title={t('auth.pages.login.title')}
            description={t('auth.pages.login.description')}
        >
            <Head title={t('auth.pages.login.page_title')} />

            <SocialAuthButtons providers={authProviders} />

            <div className="mt-2 mb-6 flex items-center">
                <div className="h-px grow bg-muted" />
                <span className="mx-4 text-xs text-muted-foreground uppercase">
                    {t('auth.pages.login.or_continue_with', 'Or continue with')}
                </span>
                <div className="h-px grow bg-muted" />
            </div>

            <Form
                action={store()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('auth.pages.login.email')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder={t(
                                        'auth.pages.login.email_placeholder',
                                    )}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">
                                        {t('auth.pages.login.password')}
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="ml-auto text-sm"
                                            tabIndex={5}
                                        >
                                            {t(
                                                'auth.pages.login.forgot_password',
                                            )}
                                        </TextLink>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder={t(
                                        'auth.pages.login.password_placeholder',
                                    )}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember">
                                    {t('auth.pages.login.remember_me')}
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner />}
                                {t('auth.pages.login.submit')}
                            </Button>
                        </div>

                        {canRegister && (
                            <div className="text-center text-sm text-muted-foreground">
                                {t('auth.pages.login.no_account')}{' '}
                                <TextLink href={register()} tabIndex={5}>
                                    {t('auth.pages.login.sign_up')}
                                </TextLink>
                            </div>
                        )}
                    </>
                )}
            </Form>

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </AuthLayout>
    );
}
