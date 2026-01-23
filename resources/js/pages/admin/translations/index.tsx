import { PaginatedDataTable } from '@/components/paginated-data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { CheckCircle2, Languages, Plus, XCircle } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type TranslationGroup = {
    key: string;
    name: string;
    status: boolean;
    missing: number;
    total: number;
    translated: number;
};

type PageProps = SharedData & {
    groups: TranslationGroup[];
    availableLanguages: Record<string, string>;
    targetLanguage: string;
};

export default function Index() {
    const { groups, availableLanguages, targetLanguage } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const [createLanguageOpen, setCreateLanguageOpen] = useState(false);
    const [languageCode, setLanguageCode] = useState('');

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('navigation.labels.translation_manager', 'Translations'),
            href: '/admin/translations',
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

    const handleCreateLanguage = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(
            '/admin/translations/language',
            { language_code: languageCode },
            {
                preserveState: true,
                onSuccess: () => {
                    setCreateLanguageOpen(false);
                    setLanguageCode('');
                },
            },
        );
    };

    const columns: ColumnDef<TranslationGroup>[] = [
        {
            accessorKey: 'name',
            header: t('admin.translations.table.translation_key', 'Translation Key'),
            cell: ({ row }) => <div className="font-medium">{row.original.name}</div>,
        },
        {
            accessorKey: 'status',
            header: t('admin.translations.table.fully_translated', 'Fully Translated'),
            cell: ({ row }) => (
                <div className="flex items-center">
                    {row.original.status ? <CheckCircle2 className="h-5 w-5 text-green-500" /> : <XCircle className="h-5 w-5 text-red-500" />}
                </div>
            ),
        },
        {
            accessorKey: 'missing',
            header: t('admin.translations.table.missing', 'Missing'),
            cell: ({ row }) => (
                <Badge variant={row.original.status ? 'default' : 'destructive'}>
                    {row.original.missing} / {row.original.total}
                </Badge>
            ),
        },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={t('navigation.labels.translation_manager', 'Translations')} />

            <div className="space-y-6 px-6 py-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('navigation.labels.translation_manager', 'Translations')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.translations.description', 'Manage translation files')}</p>
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
                        <Dialog open={createLanguageOpen} onOpenChange={setCreateLanguageOpen}>
                            <DialogTrigger asChild>
                                <Button>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('admin.translations.table.create_language', 'Create Language')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <form onSubmit={handleCreateLanguage}>
                                    <DialogHeader>
                                        <DialogTitle>{t('admin.translations.table.create_language', 'Create Language')}</DialogTitle>
                                        <DialogDescription>
                                            {t(
                                                'admin.translations.table.language_code_helper',
                                                'Enter a 2-letter language code (e.g., "fr" for French)',
                                            )}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div className="space-y-4 py-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="language_code">{t('admin.translations.table.language_code', 'Language Code')} *</Label>
                                            <Input
                                                id="language_code"
                                                value={languageCode}
                                                onChange={(e) => setLanguageCode(e.target.value.toLowerCase())}
                                                placeholder={t('admin.translations.table.language_code_placeholder', 'e.g., fr')}
                                                maxLength={2}
                                                pattern="[a-z]{2}"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button type="button" variant="outline" onClick={() => setCreateLanguageOpen(false)}>
                                            {t('common.cancel', 'Cancel')}
                                        </Button>
                                        <Button type="submit">{t('common.create', 'Create')}</Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <div className="rounded-lg border border-border bg-card p-4">
                    <PaginatedDataTable
                        columns={columns}
                        data={groups}
                        searchColumnIds={['name']}
                        searchPlaceholder={t('admin.translations.search_placeholder', 'Search translation groups...')}
                        onRowClick={(row) => {
                            router.visit(`/admin/translations/${row.key}`);
                        }}
                        enableRowSelection={false}
                    />
                </div>
            </div>
        </AdminLayout>
    );
}
