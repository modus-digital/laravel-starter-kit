import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { send } from '@/routes/verification';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { CameraIcon } from 'lucide-react';

import DeleteUser from '@/shared/components/delete-user';
import HeadingSmall from '@/shared/components/heading-small';
import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/shared/components/ui/avatar';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog';
import { Dropzone, DropzoneContent, DropzoneEmptyState } from '@/shared/components/ui/dropzone';
import AppLayout from '@/shared/layouts/app-layout';
import SettingsLayout from '@/shared/layouts/settings/layout';
import { edit } from '@/routes/profile';
import { useTranslation } from 'react-i18next';

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<SharedData>().props;
    const { t } = useTranslation();
    const [isAvatarModalOpen, setIsAvatarModalOpen] = useState(false);
    const [avatarFile, setAvatarFile] = useState<File[]>([]);
    const [isUploadingAvatar, setIsUploadingAvatar] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.profile.page_title'),
            href: edit().url,
        },
    ];

    const handleAvatarUpload = () => {
        if (avatarFile.length === 0) return;

        setIsUploadingAvatar(true);

        const formData = new FormData();
        formData.append('avatar', avatarFile[0]);
        formData.append('name', auth.user.name);
        formData.append('email', auth.user.email);

        router.patch(ProfileController.update.url(), formData, {
            preserveScroll: true,
            preserveState: false, // Force reload of shared data to get updated avatar
            onSuccess: () => {
                setIsAvatarModalOpen(false);
                setAvatarFile([]);
            },
            onFinish: () => {
                setIsUploadingAvatar(false);
            },
        });
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.profile.page_title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={t('settings.profile.title')} description={t('settings.profile.description')} />

                    <div className="mb-6">
                        <Label className="mb-3 block">{t('settings.profile.avatar')}</Label>
                        <div className="flex items-center gap-4">
                            <Avatar className="size-20">
                                {auth.user.avatar && (
                                    <AvatarImage
                                        src={auth.user.avatar}
                                        alt={auth.user.name}
                                        key={auth.user.avatar}
                                    />
                                )}
                                <AvatarFallback className="text-lg">{getInitials(auth.user.name)}</AvatarFallback>
                            </Avatar>
                            <Button variant="outline" onClick={() => setIsAvatarModalOpen(true)} type="button">
                                <CameraIcon className="mr-2 size-4" />
                                {t('settings.profile.change_avatar')}
                            </Button>
                        </div>
                    </div>

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">{t('settings.profile.name')}</Label>

                                    <Input
                                        id="name"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder={t('settings.profile.name_placeholder')}
                                    />

                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">{t('settings.profile.email')}</Label>

                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder={t('settings.profile.email_placeholder')}
                                    />

                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                {mustVerifyEmail && auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            {t('settings.profile.unverified')}{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                {t('settings.profile.click_to_resend')}
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">{t('settings.profile.verification_sent')}</div>
                                        )}
                                    </div>
                                )}

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing} data-test="update-profile-button">
                                        {t('settings.profile.save')}
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">{t('settings.profile.saved')}</p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>

                    <Dialog open={isAvatarModalOpen} onOpenChange={setIsAvatarModalOpen}>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('settings.profile.change_avatar')}</DialogTitle>
                                <DialogDescription>{t('settings.profile.avatar_description')}</DialogDescription>
                            </DialogHeader>

                            <div className="space-y-4">
                                <Dropzone
                                    src={avatarFile}
                                    accept={{ 'image/*': ['.png', '.jpg', '.jpeg', '.gif', '.webp'] }}
                                    maxSize={2 * 1024 * 1024}
                                    onDrop={(files) => setAvatarFile(files)}
                                >
                                    <DropzoneContent />
                                    <DropzoneEmptyState />
                                </Dropzone>

                                {avatarFile.length > 0 && (
                                    <div className="flex justify-center">
                                        <img
                                            src={URL.createObjectURL(avatarFile[0])}
                                            alt="Preview"
                                            className="max-h-48 rounded-lg object-contain"
                                        />
                                    </div>
                                )}
                            </div>

                            <DialogFooter>
                                <Button variant="outline" onClick={() => setIsAvatarModalOpen(false)} type="button">
                                    {t('settings.profile.cancel')}
                                </Button>
                                <Button
                                    onClick={handleAvatarUpload}
                                    disabled={avatarFile.length === 0 || isUploadingAvatar}
                                    type="button"
                                >
                                    {isUploadingAvatar ? t('settings.profile.uploading') : t('settings.profile.upload')}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
