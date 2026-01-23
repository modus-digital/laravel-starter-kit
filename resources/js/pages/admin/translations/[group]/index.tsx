import { PaginatedDataTable } from '@/components/paginated-data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { ArrowLeft, Bolt, CheckCircle2, Edit, Languages } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Translation = {
    key: string;
    full_key: string;
    english: string;
    translation: string;
};

type PageProps = SharedData & {
    group: string;
    groupName: string;
    translations: Translation[];
    availableLanguages: Record<string, string>;
    targetLanguage: string;
    progress: {
        missing: number;
        total: number;
        translated: number;
    };
};

export default function GroupIndex() {
    const { group, groupName, translations, availableLanguages, targetLanguage, progress } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [editingTranslation, setEditingTranslation] = useState<Translation | null>(null);
    const [editValue, setEditValue] = useState('');

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('navigation.labels.translation_manager', 'Translations'),
            href: '/admin/translations',
        },
        {
            title: groupName,
            href: `/admin/translations/${group}`,
        },
    ];

    const handleLanguageChange = (value: string) => {
        router.post(
            '/admin/translations/target-language',
            { language: value },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleEdit = (translation: Translation) => {
        setEditingTranslation(translation);
        setEditValue(translation.translation);
        setEditDialogOpen(true);
    };

    const handleSaveEdit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingTranslation) {
            return;
        }

        router.put(
            `/admin/translations/${group}`,
            {
                key: editingTranslation.full_key,
                translation: editValue,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    setEditDialogOpen(false);
                    setEditingTranslation(null);
                    setEditValue('');
                },
            },
        );
    };

    const columns: ColumnDef<Translation>[] = [
        {
            accessorKey: 'english',
            header: t('admin.translations.quick_translate.base', 'English'),
            cell: ({ row }) => (
                <div>
                    <div className="font-medium">{row.original.english}</div>
                    <div className="mt-1 text-xs text-muted-foreground">{row.original.full_key}</div>
                </div>
            ),
        },
        {
            accessorKey: 'translation',
            header: targetLanguage.toUpperCase(),
            cell: ({ row }) => (
                <div className="flex items-center justify-between">
                    <div className={row.original.translation ? '' : 'text-muted-foreground italic'}>
                        {row.original.translation || t('admin.translations.quick_translate.base_placeholder', 'Not translated')}
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                            e.stopPropagation();
                            handleEdit(row.original);
                        }}
                        className="ml-2"
                    >
                        <Edit className="h-4 w-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`${groupName} - ${t('navigation.labels.translation_manager', 'Translations')}`} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center gap-4">
                    <Link href="/admin/translations">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div className="flex-1">
                        <h1 className="text-2xl font-semibold">{groupName}</h1>
                        <p className="text-sm text-muted-foreground">
                            {t('admin.translations.group_description', 'Manage translations for this group')}
                        </p>
                    </div>
                    <div className="flex items-center gap-4">
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
                        {progress.missing > 0 && (
                            <Link href={`/admin/translations/${group}/quick-translate`}>
                                <Button>
                                    <Bolt className="mr-2 h-4 w-4" />
                                    {t('admin.translations.quick_translate.quick_translate', 'Quick Translate')}
                                </Button>
                            </Link>
                        )}
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex items-center gap-2">
                                <CheckCircle2 className={`h-5 w-5 ${progress.missing === 0 ? 'text-green-500' : 'text-muted-foreground'}`} />
                                <span className="text-sm font-medium">{t('admin.translations.progress', 'Progress')}</span>
                            </div>
                            <Badge variant={progress.missing === 0 ? 'default' : 'destructive'}>
                                {progress.translated} / {progress.total}
                            </Badge>
                            {progress.missing > 0 && (
                                <span className="text-sm text-muted-foreground">
                                    {progress.missing} {t('admin.translations.missing', 'missing')}
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <PaginatedDataTable
                        columns={columns}
                        data={translations}
                        searchColumnIds={['english', 'translation']}
                        searchPlaceholder={t('admin.translations.search_translations_placeholder', 'Search translations...')}
                        enableRowSelection={false}
                    />
                </div>

                <Dialog open={editDialogOpen} onOpenChange={setEditDialogOpen}>
                    <DialogContent className="max-w-2xl">
                        <form onSubmit={handleSaveEdit}>
                            <DialogHeader>
                                <DialogTitle>{t('admin.translations.quick_translate.edit', 'Edit Translation')}</DialogTitle>
                                <DialogDescription>{editingTranslation?.full_key}</DialogDescription>
                            </DialogHeader>
                            <div className="space-y-4 py-4">
                                <div className="space-y-2">
                                    <Label>{t('admin.translations.quick_translate.base', 'English')}</Label>
                                    <div className="rounded-md border border-border bg-muted p-3 text-sm">{editingTranslation?.english}</div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="translation">{availableLanguages[targetLanguage] || targetLanguage.toUpperCase()} *</Label>
                                    <textarea
                                        id="translation"
                                        value={editValue}
                                        onChange={(e) => setEditValue(e.target.value)}
                                        className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                        autoFocus
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => setEditDialogOpen(false)}>
                                    {t('common.cancel', 'Cancel')}
                                </Button>
                                <Button type="submit">{t('common.save', 'Save')}</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </AdminLayout>
    );
}
