import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { store } from '@/routes/password/confirm';
import { Form, Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function ConfirmPassword() {
    const { t } = useTranslation();

    return (
        <AuthLayout title={t('auth.pages.confirm_password.title')} description={t('auth.pages.confirm_password.description')}>
            <Head title={t('auth.pages.confirm_password.page_title')} />

            <Form action={store()} resetOnSuccess={['password']}>
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">{t('auth.pages.confirm_password.password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                placeholder={t('auth.pages.confirm_password.password_placeholder')}
                                autoComplete="current-password"
                                autoFocus
                            />

                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center">
                            <Button className="w-full" disabled={processing} data-test="confirm-password-button">
                                {processing && <Spinner />}
                                {t('auth.pages.confirm_password.submit')}
                            </Button>
                        </div>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
