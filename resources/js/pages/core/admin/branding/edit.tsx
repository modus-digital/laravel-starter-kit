import InputError from '@/shared/components/input-error';
import { Button } from '@/shared/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { ColorPalettePreview } from '@/shared/components/ui/color-palette-preview';
import { ColorPicker } from '@/shared/components/ui/color-picker';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/shared/components/ui/select';
import { Textarea } from '@/shared/components/ui/textarea';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type SharedData } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Eye, Upload } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Branding = {
    logo_light: string | null;
    logo_dark: string | null;
    emblem_light: string | null;
    emblem_dark: string | null;
    app_name: string;
    tagline: string | null;
    primary_color: string;
    secondary_color: string;
    font: string;
};

type PageProps = SharedData & {
    branding: Branding;
};

/** Use value as-is if it's already a full URL, otherwise treat as storage path and prepend /storage/ */
function brandingAssetPreviewUrl(value: string | null): string | null {
    if (!value) return null;
    if (value.startsWith('http://') || value.startsWith('https://')) return value;
    return `/storage/${value}`;
}

export default function Edit({ branding }: PageProps) {
    const { t } = useTranslation();
    const [logoLightPreview, setLogoLightPreview] = useState<string | null>(brandingAssetPreviewUrl(branding.logo_light));
    const [logoDarkPreview, setLogoDarkPreview] = useState<string | null>(brandingAssetPreviewUrl(branding.logo_dark));
    const [emblemLightPreview, setEmblemLightPreview] = useState<string | null>(brandingAssetPreviewUrl(branding.emblem_light));
    const [emblemDarkPreview, setEmblemDarkPreview] = useState<string | null>(brandingAssetPreviewUrl(branding.emblem_dark));
    const [palettePreviewOpen, setPalettePreviewOpen] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        logo_light: null as File | null,
        logo_dark: null as File | null,
        emblem_light: null as File | null,
        emblem_dark: null as File | null,
        app_name: branding.app_name,
        tagline: branding.tagline || '',
        primary_color: branding.primary_color,
        secondary_color: branding.secondary_color,
        font: branding.font,
        _method: 'PUT',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/branding');
    };

    const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>, variant: 'light' | 'dark') => {
        const file = e.target.files?.[0];
        if (file) {
            const fieldName = variant === 'light' ? 'logo_light' : 'logo_dark';
            const setPreview = variant === 'light' ? setLogoLightPreview : setLogoDarkPreview;

            setData(fieldName, file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleEmblemChange = (e: React.ChangeEvent<HTMLInputElement>, variant: 'light' | 'dark') => {
        const file = e.target.files?.[0];
        if (file) {
            const fieldName = variant === 'light' ? 'emblem_light' : 'emblem_dark';
            const setPreview = variant === 'light' ? setEmblemLightPreview : setEmblemDarkPreview;

            setData(fieldName, file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreview(reader.result as string);
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
                        {/* Row 1 & 2: Logo+Emblems (left) | Colors + Typography stacked (right) */}
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {/* Left: Logo & Emblems combined in one card */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        {t('admin.branding.sections.logos')} &amp; {t('admin.branding.sections.emblems')}
                                    </CardTitle>
                                    <CardDescription>
                                        {t('admin.branding.descriptions.logos')}. {t('admin.branding.descriptions.emblems')}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    <div className="space-y-2">
                                        <h3 className="text-sm font-medium text-muted-foreground">{t('admin.branding.sections.logos')}</h3>
                                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="logo_light">{t('admin.branding.labels.logo_light')}</Label>
                                                {logoLightPreview && (
                                                    <div className="flex h-16 w-full items-center justify-center rounded border bg-white">
                                                        <img
                                                            src={logoLightPreview}
                                                            alt="Light logo"
                                                            className="h-16 w-auto max-w-full object-contain"
                                                        />
                                                    </div>
                                                )}
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => document.getElementById('logo_light')?.click()}
                                                    className="w-full"
                                                >
                                                    <Upload className="mr-2 h-4 w-4" />
                                                    {t('admin.branding.upload_logo_light')}
                                                </Button>
                                                <input
                                                    id="logo_light"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => handleLogoChange(e, 'light')}
                                                    className="hidden"
                                                />
                                                <InputError message={errors.logo_light} />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="logo_dark">{t('admin.branding.labels.logo_dark')}</Label>
                                                {logoDarkPreview && (
                                                    <div className="flex h-16 w-full items-center justify-center rounded border bg-gray-900">
                                                        <img
                                                            src={logoDarkPreview}
                                                            alt="Dark logo"
                                                            className="h-16 w-auto max-w-full object-contain"
                                                        />
                                                    </div>
                                                )}
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => document.getElementById('logo_dark')?.click()}
                                                    className="w-full"
                                                >
                                                    <Upload className="mr-2 h-4 w-4" />
                                                    {t('admin.branding.upload_logo_dark')}
                                                </Button>
                                                <input
                                                    id="logo_dark"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => handleLogoChange(e, 'dark')}
                                                    className="hidden"
                                                />
                                                <InputError message={errors.logo_dark} />
                                            </div>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-sm font-medium text-muted-foreground">{t('admin.branding.sections.emblems')}</h3>
                                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="emblem_light">{t('admin.branding.labels.emblem_light')}</Label>
                                                {emblemLightPreview && (
                                                    <div className="flex h-16 w-full items-center justify-center rounded border bg-white">
                                                        <img
                                                            src={emblemLightPreview}
                                                            alt="Light emblem"
                                                            className="h-16 w-auto max-w-full object-contain"
                                                        />
                                                    </div>
                                                )}
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => document.getElementById('emblem_light')?.click()}
                                                    className="w-full"
                                                >
                                                    <Upload className="mr-2 h-4 w-4" />
                                                    {t('admin.branding.upload_emblem_light')}
                                                </Button>
                                                <input
                                                    id="emblem_light"
                                                    type="file"
                                                    accept="image/jpeg,image/jpg,image/png,image/svg+xml,image/webp"
                                                    onChange={(e) => handleEmblemChange(e, 'light')}
                                                    className="hidden"
                                                />
                                                <InputError message={errors.emblem_light} />
                                            </div>
                                            <div className="flex flex-col gap-2">
                                                <Label htmlFor="emblem_dark">{t('admin.branding.labels.emblem_dark')}</Label>
                                                {emblemDarkPreview && (
                                                    <div className="flex h-16 w-full items-center justify-center rounded border bg-gray-900">
                                                        <img
                                                            src={emblemDarkPreview}
                                                            alt="Dark emblem"
                                                            className="h-16 w-auto max-w-full object-contain"
                                                        />
                                                    </div>
                                                )}
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => document.getElementById('emblem_dark')?.click()}
                                                    className="w-full"
                                                >
                                                    <Upload className="mr-2 h-4 w-4" />
                                                    {t('admin.branding.upload_emblem_dark')}
                                                </Button>
                                                <input
                                                    id="emblem_dark"
                                                    type="file"
                                                    accept="image/jpeg,image/jpg,image/png,image/svg+xml,image/webp"
                                                    onChange={(e) => handleEmblemChange(e, 'dark')}
                                                    className="hidden"
                                                />
                                                <InputError message={errors.emblem_dark} />
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Right: Colors and Typography stacked, justified to match left card height */}
                            <div className="flex min-h-0 flex-col justify-between gap-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>{t('admin.branding.sections.colors')}</CardTitle>
                                        <CardDescription>{t('admin.branding.descriptions.colors')}</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label>{t('admin.branding.labels.primary_color')}</Label>
                                                <ColorPicker color={data.primary_color} onChange={(value) => setData('primary_color', value)} />
                                                <InputError message={errors.primary_color} />
                                            </div>
                                            <div className="space-y-2">
                                                <Label>{t('admin.branding.labels.secondary_color')}</Label>
                                                <ColorPicker color={data.secondary_color} onChange={(value) => setData('secondary_color', value)} />
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
                                                        <SelectLabel className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                            Sans-Serif
                                                        </SelectLabel>
                                                        {sansSerifFonts.map((font) => (
                                                            <SelectItem key={font.value} value={font.value}>
                                                                <span style={{ fontFamily: font.label }}>{font.label}</span>
                                                            </SelectItem>
                                                        ))}
                                                    </SelectGroup>
                                                    <SelectGroup>
                                                        <SelectLabel className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                            Serif
                                                        </SelectLabel>
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
                        </div>

                        {/* Row 3: General Information - Full width */}
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
                                {processing ? t('common.status.saving') : t('common.actions.save')}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>

            <ColorPalettePreview
                open={palettePreviewOpen}
                onOpenChange={setPalettePreviewOpen}
                primaryColor={data.primary_color}
                secondaryColor={data.secondary_color}
            />
        </AdminLayout>
    );
}
