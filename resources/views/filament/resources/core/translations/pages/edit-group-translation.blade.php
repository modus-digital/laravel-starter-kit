<x-filament-panels::page>
    @livewire(
        \App\Filament\Resources\Core\Translations\Widgets\TranslationsGroupTableWidget::class,
        [
            'group' => request()->route('group'),
        ]
    )
</x-filament-panels::page>