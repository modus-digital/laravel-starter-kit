<?php

namespace App\Filament\Resources\Core\Translations\Widgets;

use App\Enums\Language;
use App\Filament\Resources\Core\Translations\TranslationService;
use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class LanguageSelector extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.resources.core.translations.widgets.language-selector';

    protected int|string|array $columnSpan = 'full';

    public array $languageOptions = [];

    public string $language;

    public function mount(TranslationService $service): void
    {
        $this->languageOptions = collect($service->getAvailableLanguages())
            ->mapWithKeys(fn (string $code) => [$code => Language::from($code)->label()])
            ->all();

        $this->language = $service->getTargetLanguage();
    }

    public function updatedLanguage(string $language): void
    {
        if (! array_key_exists($language, $this->languageOptions)) {
            return;
        }

        app(TranslationService::class)->setTargetLanguage($language);

        $this->dispatch('translations-language-changed');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('language')
                    ->label(null)
                    ->hiddenLabel()
                    ->options($this->languageOptions)
                    ->default(fn () => session('translations.target_language', 'en'))
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(fn (string $state) => $this->updatedLanguage($state))
                    ->extraAttributes(['class' => 'min-w-36'])
                    ->required(),
            ]);
    }
}
