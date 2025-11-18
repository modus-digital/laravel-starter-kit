<?php

namespace App\Filament\Resources\Core\Translations\Pages;

use App\Filament\Resources\Core\Translations\Tables\TranslationsGroupTable;
use App\Filament\Resources\Core\Translations\TranslationResource;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EditGroupTranslation extends Page
{
    protected static string $resource = TranslationResource::class;

    protected string $view = 'filament.resources.core.translations.pages.edit-group-translation';

    public function getBreadcrumbs(): array
    {
        return [
            TranslationResource::getUrl('index') => 'Translations',
            TranslationResource::getUrl('group', ['group' => request()->route('group')]) => 'Translation Groups',
            null => Str::headline(request()->route('group')),
        ];
    }

    public function getTitle(): string
    {
        return Str::headline(request()->route('group')).' Translations';
    }

    public function table(Table $table): Table
    {
        return TranslationsGroupTable::configure($table, request()->route('group'));
    }
}
