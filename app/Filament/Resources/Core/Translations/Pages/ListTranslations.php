<?php

namespace App\Filament\Resources\Core\Translations\Pages;

use App\Filament\Resources\Core\Translations\TranslationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class ListTranslations extends ListRecords
{
    protected static string $resource = TranslationResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            TranslationResource::getUrl('index') => 'Translations',
            null => 'Translation Groups',
        ];
    }

    public function mount(): void
    {
        parent::mount();
    }

    #[On('translations-language-changed')]
    public function refreshTranslationsTable(): void
    {
        $this->resetTable();
    }

    protected function makeTable(): Table
    {
        return parent::makeTable()->query(null);
    }
}
