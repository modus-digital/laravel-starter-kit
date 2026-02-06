import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Spinner } from '@/shared/components/ui/spinner';
import AuthLayout from '@/shared/layouts/auth-layout';
import { useTranslation } from 'react-i18next';

interface ResetPasswordProps {
    token: string;
    email: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const { t } = useTranslation();

    return (
        <AuthLayout title={t('auth.pages.reset_password.title')} description={t('auth.pages.reset_password.description')}>
            <Head title={t('auth.pages.reset_password.page_title')} />

            <Form action={update()} transform={(data) => ({ ...data, token, email })} resetOnSuccess={['password', 'password_confirmation']}>
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="email">{t('auth.pages.reset_password.email')}</Label>
                            <Input id="email" type="email" name="email" autoComplete="email" value={email} className="mt-1 block w-full" readOnly />
                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">{t('auth.pages.reset_password.password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                autoFocus
                                placeholder={t('auth.pages.reset_password.password_placeholder')}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">{t('auth.pages.reset_password.password_confirmation')}</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                placeholder={t('auth.pages.reset_password.password_confirmation_placeholder')}
                            />
                            <InputError message={errors.password_confirmation} className="mt-2" />
                        </div>

                        <Button type="submit" className="mt-4 w-full" disabled={processing} data-test="reset-password-button">
                            {processing && <Spinner />}
                            {t('auth.pages.reset_password.submit')}
                        </Button>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
