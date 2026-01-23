import AdminLayout from '@/layouts/admin/layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ColorPicker } from '@/components/ui/color-picker';
import { ColorPalettePreview } from '@/components/ui/color-palette-preview';
import { ImageCropModal } from '@/components/ui/image-crop-modal';
import { type SharedData } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Eye, Upload } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import InputError from '@/components/input-error';

type Branding = {
    logo: string | null;
    favicon: string | null;
    app_name: string;
    tagline: string | null;
    primary_color: string;
    secondary_color: string;
    font: string;
    logo_aspect_ratio?: '1:1' | '16:9';
};

type PageProps = SharedData & {
    branding: Branding;
};

export default function Edit({ branding }: PageProps) {
    const { t } = useTranslation();
    const [logoPreview, setLogoPreview] = useState<string | null>(
        branding.logo ? `/storage/${branding.logo}` : null
    );
    const [faviconPreview, setFaviconPreview] = useState<string | null>(
        branding.favicon ? `/storage/${branding.favicon}` : null
    );
    const [cropModalOpen, setCropModalOpen] = useState(false);
    const [imageToCrop, setImageToCrop] = useState<string | null>(null);
    const [pendingLogoFile, setPendingLogoFile] = useState<File | null>(null);
    const [palettePreviewOpen, setPalettePreviewOpen] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        logo: null as File | null,
        favicon: null as File | null,
        app_name: branding.app_name,
        tagline: branding.tagline || '',
        primary_color: branding.primary_color,
        secondary_color: branding.secondary_color,
        font: branding.font,
        logo_aspect_ratio: (branding.logo_aspect_ratio || '1:1') as '1:1' | '16:9',
        _method: 'PUT',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/branding');
    };

    const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setPendingLogoFile(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setImageToCrop(reader.result as string);
                setCropModalOpen(true);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleCropComplete = (croppedBlob: Blob, aspectRatio: '1:1' | '16:9') => {
        const croppedFile = new File([croppedBlob], pendingLogoFile?.name || 'logo.png', {
            type: croppedBlob.type,
        });
        setData('logo', croppedFile);
        setData('logo_aspect_ratio', aspectRatio);
        const reader = new FileReader();
        reader.onloadend = () => {
            setLogoPreview(reader.result as string);
        };
        reader.readAsDataURL(croppedBlob);
        setPendingLogoFile(null);
        setImageToCrop(null);
    };

    const handleFaviconChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('favicon', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setFaviconPreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const sansSerifFonts = [
        { value: 'inter', label: 'Inter' },
        { value: 'roboto', label: 'Roboto' },
        { value: 'poppins', label: 'Poppins' },
    ];

    const serifFonts = [
        { value: 'arvo', label: 'Arvo' },
        { value: 'inria_serif', label: 'Inria Serif' },
        { value: 'lato', label: 'Lato' },
    ];

    return (
        <AdminLayout>
            <Head title={t('admin.branding.title')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.branding.title')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.branding.description')}</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Row 1: Logo, Colors, Font - 3 columns */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Logo Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.branding.sections.logo')}</CardTitle>
                                <CardDescription>{t('admin.branding.descriptions.logo')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="logo">{t('admin.branding.labels.logo')}</Label>
                                        {logoPreview && (
                                            <div className="flex h-16 w-full items-center justify-center rounded border bg-muted/30">
                                                <img
                                                    src={logoPreview}
                                                    alt="Logo preview"
                                                    className="h-16 w-auto max-w-full object-contain"
                                                />
                                            </div>
                                        )}
                                        <div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => document.getElementById('logo')?.click()}
                                                className="w-full sm:w-auto"
                                            >
                                                <Upload className="mr-2 h-4 w-4" />
                                                {t('admin.branding.upload_logo')}
                                            </Button>
                                            <input
                                                id="logo"
                                                type="file"
                                                accept="image/*"
                                                onChange={handleLogoChange}
                                                className="hidden"
                                            />
                                        </div>
                                        <p className="text-xs text-muted-foreground">{t('admin.branding.helpers.logo')}</p>
                                        <InputError message={errors.logo} />
                                    </div>

                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="favicon">{t('admin.branding.labels.favicon')}</Label>
                                        {faviconPreview && (
                                            <div className="flex h-16 w-full items-center justify-center rounded border bg-muted/30">
                                                <img
                                                    src={faviconPreview}
                                                    alt="Favicon preview"
                                                    className="h-16 w-auto max-w-full object-contain"
                                                />
                                            </div>
                                        )}
                                        <div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => document.getElementById('favicon')?.click()}
                                                className="w-full sm:w-auto"
                                            >
                                                <Upload className="mr-2 h-4 w-4" />
                                                {t('admin.branding.upload_favicon')}
                                            </Button>
                                            <input
                                                id="favicon"
                                                type="file"
                                                accept="image/x-icon,image/png,image/svg+xml"
                                                onChange={handleFaviconChange}
                                                className="hidden"
                                            />
                                        </div>
                                        <p className="text-xs text-muted-foreground">{t('admin.branding.helpers.favicon')}</p>
                                        <InputError message={errors.favicon} />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Colors Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.branding.sections.colors')}</CardTitle>
                                <CardDescription>{t('admin.branding.descriptions.colors')}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label>{t('admin.branding.labels.primary_color')}</Label>
                                        <ColorPicker
                                            color={data.primary_color}
                                            onChange={(value) => setData('primary_color', value)}
                                        />
                                        <InputError message={errors.primary_color} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>{t('admin.branding.labels.secondary_color')}</Label>
                                        <ColorPicker
                                            color={data.secondary_color}
                                            onChange={(value) => setData('secondary_color', value)}
                                        />
                                        <InputError message={errors.secondary_color} />
                                    </div>
                                </div>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setPalettePreviewOpen(true)}
                                    className="w-full"
                                >
                                    <Eye className="mr-2 h-4 w-4" />
                                    {t('admin.branding.preview_palette')}
                                </Button>
                            </CardContent>
                        </Card>

                        {/* Font Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.branding.sections.typography')}</CardTitle>
                                <CardDescription>{t('admin.branding.descriptions.typography')}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="font">{t('admin.branding.labels.font')}</Label>
                                    <Select value={data.font} onValueChange={(value) => setData('font', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sans-Serif</SelectLabel>
                                                {sansSerifFonts.map((font) => (
                                                    <SelectItem key={font.value} value={font.value}>
                                                        <span style={{ fontFamily: font.label }}>{font.label}</span>
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                            <SelectGroup>
                                                <SelectLabel className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Serif</SelectLabel>
                                                {serifFonts.map((font) => (
                                                    <SelectItem key={font.value} value={font.value}>
                                                        <span style={{ fontFamily: font.label }}>{font.label}</span>
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.font} />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Row 2: General Information - Full width */}
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('admin.branding.sections.general')}</CardTitle>
                            <CardDescription>{t('admin.branding.descriptions.general')}</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="app_name">{t('admin.branding.labels.app_name')}</Label>
                                <Input
                                    id="app_name"
                                    value={data.app_name}
                                    onChange={(e) => setData('app_name', e.target.value)}
                                    placeholder={t('admin.branding.placeholders.app_name')}
                                />
                                <InputError message={errors.app_name} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="tagline">{t('admin.branding.labels.tagline')}</Label>
                                <Textarea
                                    id="tagline"
                                    value={data.tagline}
                                    onChange={(e) => setData('tagline', e.target.value)}
                                    placeholder={t('admin.branding.placeholders.tagline')}
                                    rows={3}
                                />
                                <InputError message={errors.tagline} />
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing} className="w-full sm:w-auto">
                            {processing ? t('common.saving') : t('admin.branding.labels.save')}
                        </Button>
                    </div>
                </form>
            </div>
            </div>

            <ImageCropModal
                open={cropModalOpen}
                onOpenChange={setCropModalOpen}
                imageSrc={imageToCrop}
                onCropComplete={handleCropComplete}
                initialAspectRatio={data.logo_aspect_ratio}
            />

            <ColorPalettePreview
                open={palettePreviewOpen}
                onOpenChange={setPalettePreviewOpen}
                primaryColor={data.primary_color}
                secondaryColor={data.secondary_color}
            />
        </AdminLayout>
    );
}
