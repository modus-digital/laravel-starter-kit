import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/react';

import InputError from '@/shared/components/input-error';
import TextLink from '@/shared/components/text-link';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Spinner } from '@/shared/components/ui/spinner';
import AuthLayout from '@/shared/layouts/auth-layout';
import { useTranslation } from 'react-i18next';

export default function Register() {
    const { t } = useTranslation();

    return (
        <AuthLayout title={t('auth.pages.register.title')} description={t('auth.pages.register.description')}>
            <Head title={t('auth.pages.register.page_title')} />
            <Form action={store()} resetOnSuccess={['password', 'password_confirmation']} disableWhileProcessing className="flex flex-col gap-6">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">{t('auth.pages.register.name')}</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder={t('auth.pages.register.name_placeholder')}
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">{t('auth.pages.register.email')}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder={t('auth.pages.register.email_placeholder')}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">{t('auth.pages.register.password')}</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder={t('auth.pages.register.password_placeholder')}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">{t('auth.pages.register.password_confirmation')}</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder={t('auth.pages.register.password_confirmation_placeholder')}
                                />
                                <InputError message={errors.password_confirmation} />
                            </div>

                            <Button type="submit" className="mt-2 w-full" tabIndex={5} data-test="register-user-button">
                                {processing && <Spinner />}
                                {t('auth.pages.register.submit')}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {t('auth.pages.register.have_account')}{' '}
                            <TextLink href={login()} tabIndex={6}>
                                {t('auth.pages.register.login')}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
