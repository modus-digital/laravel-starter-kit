<?php

namespace App\Filament\Resources\Core\Translations\Widgets;

use App\Filament\Resources\Core\Translations\Tables\TranslationsGroupTable as TableDefinition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Attributes\On;

class TranslationsGroupTableWidget extends TableWidget
{
    public string $group;

    protected static ?string $heading = null;

    public function mount(string $group): void
    {
        $this->group = $group;
    }

    public function table(Table $table): Table
    {
        return TableDefinition::configure($table, $this->group);
    }

    #[On('translations-language-changed')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }
}
