// Components
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import TextLink from '@/shared/components/text-link';
import { Button } from '@/shared/components/ui/button';
import { Spinner } from '@/shared/components/ui/spinner';
import AuthLayout from '@/shared/layouts/auth-layout';
import { Form, Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();

    return (
        <AuthLayout title={t('auth.pages.verify_email.title')} description={t('auth.pages.verify_email.description')}>
            <Head title={t('auth.pages.verify_email.page_title')} />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">{t('auth.pages.verify_email.sent')}</div>
            )}

            <Form action={send()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            {t('auth.pages.verify_email.resend')}
                        </Button>

                        <TextLink href={logout()} className="mx-auto block text-sm">
                            {t('auth.pages.verify_email.logout')}
                        </TextLink>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
