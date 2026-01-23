import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Languages, Save, CheckCircle2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useState, useEffect, useRef } from 'react';

type PageProps = SharedData & {
    group: string;
    groupName: string;
    currentKey: string;
    currentEnglish: string;
    remainingCount: number;
    availableLanguages: Record<string, string>;
    targetLanguage: string;
    progress: {
        missing: number;
        total: number;
        translated: number;
    };
};

export default function QuickTranslate() {
    const {
        group,
        groupName,
        currentKey,
        currentEnglish,
        remainingCount,
        availableLanguages,
        targetLanguage,
        progress,
    } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [translation, setTranslation] = useState('');
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('navigation.labels.translation_manager', 'Translations'),
            href: '/admin/translations',
        },
        {
            title: groupName,
            href: `/admin/translations/${group}`,
        },
        {
            title: t('admin.translations.quick_translate.title', 'Quick Translate'),
            href: `/admin/translations/${group}/quick-translate`,
        },
    ];

    useEffect(() => {
        // Reset translation and focus textarea when currentKey changes
        setTranslation('');
        if (textareaRef.current) {
            textareaRef.current.focus();
        }
    }, [currentKey]);

    const handleLanguageChange = (value: string) => {
        router.post(
            '/admin/translations/target-language',
            { language: value },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(
            `/admin/translations/${group}/quick-translate`,
            {
                translation_key: currentKey,
                translation: translation,
            },
            {
                onSuccess: () => {
                    setTranslation('');
                },
            }
        );
    };

    const progressPercentage = progress.total > 0 ? (progress.translated / progress.total) * 100 : 0;

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`${t('admin.translations.quick_translate.title', 'Quick Translate')} - ${groupName}`} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href={`/admin/translations/${group}`}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex-1">
                        <h1 className="text-2xl font-semibold">
                            {t('admin.translations.quick_translate.title', 'Quick Translate')}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {t('admin.translations.quick_translate.description', 'Translate missing keys one by one')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Languages className="h-4 w-4 text-muted-foreground" />
                        <Select value={targetLanguage} onValueChange={handleLanguageChange}>
                            <SelectTrigger className="w-[180px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(availableLanguages).map(([code, label]) => (
                                    <SelectItem key={code} value={code}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-4">
                            <span className="text-sm font-medium">
                                {t('admin.translations.progress', 'Progress')}
                            </span>
                            <Badge variant={progress.missing === 0 ? 'default' : 'destructive'}>
                                {progress.translated} / {progress.total}
                            </Badge>
                            {remainingCount > 0 && (
                                <span className="text-sm text-muted-foreground">
                                    {remainingCount} {t('admin.translations.remaining', 'remaining')}
                                </span>
                            )}
                        </div>
                        {progress.missing === 0 && (
                            <div className="flex items-center gap-2 text-green-500">
                                <CheckCircle2 className="h-5 w-5" />
                                <span className="text-sm font-medium">
                                    {t('admin.translations.all_complete', 'All translations complete!')}
                                </span>
                            </div>
                        )}
                    </div>
                    <div className="w-full bg-muted rounded-full h-2">
                        <div
                            className="bg-primary h-2 rounded-full transition-all duration-300"
                            style={{ width: `${progressPercentage}%` }}
                        />
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label className="text-xs text-muted-foreground uppercase">
                                {t('admin.translations.translation_key', 'Translation Key')}
                            </Label>
                            <div className="rounded-md border border-border bg-muted p-3 text-sm font-mono break-all">
                                {currentKey}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label>
                                {t('admin.translations.quick_translate.base', 'English')}
                            </Label>
                            <div className="rounded-md border border-border bg-muted p-4 text-sm min-h-[80px]">
                                {currentEnglish}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="translation">
                                {availableLanguages[targetLanguage] || targetLanguage.toUpperCase()} *
                            </Label>
                            <textarea
                                ref={textareaRef}
                                id="translation"
                                name="translation"
                                value={translation}
                                onChange={(e) => setTranslation(e.target.value)}
                                className="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder={t('admin.translations.quick_translate.placeholder', 'Enter translation...')}
                                required
                                autoFocus
                            />
                        </div>

                        <div className="flex justify-end gap-2">
                            <Link href={`/admin/translations/${group}`}>
                                <Button type="button" variant="outline">
                                    {t('common.cancel', 'Cancel')}
                                </Button>
                            </Link>
                            <Button type="submit">
                                <Save className="mr-2 h-4 w-4" />
                                {remainingCount > 1
                                    ? t('admin.translations.save_and_next', 'Save & Next')
                                    : t('admin.translations.save', 'Save')}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
