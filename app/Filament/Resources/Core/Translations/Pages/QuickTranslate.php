<?php

namespace App\Filament\Resources\Core\Translations\Pages;

use App\Enums\Language;
use App\Filament\Resources\Core\Translations\TranslationResource;
use App\Filament\Resources\Core\Translations\TranslationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Str;

class QuickTranslate extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = TranslationResource::class;

    protected string $view = 'filament.resources.core.translations.pages.quick-translate';

    public ?array $data = [];

    public array $missingTranslations = [];

    public ?string $currentKey = null;

    public function mount(): void
    {
        $service = app()->make(TranslationService::class);
        $targetLanguage = $service->getTargetLanguage();
        $group = request()->route('group');

        $this->missingTranslations = $service->getMissingTranslations($targetLanguage, $group);

        if (empty($this->missingTranslations)) {
            Notification::make()
                ->title('All translations complete')
                ->body('All translations for this group have been completed.')
                ->success()
                ->send();

            $this->redirect(TranslationResource::getUrl('group', ['group' => $group]));

            return;
        }

        $this->currentKey = array_key_first($this->missingTranslations);

        $this->form->fill([
            'translation_key' => $this->currentKey,
            'english' => $this->missingTranslations[$this->currentKey] ?? '',
            'translation' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $service = app()->make(TranslationService::class);

        return $schema
            ->components([
                Hidden::make('translation_key'),
                Section::make('Base')
                    ->description(fn (): ?string => $this->currentKey)
                    ->schema([
                        Textarea::make('english')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2)
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make(Language::from($service->getTargetLanguage())->label())
                    ->schema([
                        Textarea::make('translation')
                            ->hiddenLabel()
                            ->rows(2)
                            ->required()
                            ->extraInputAttributes(['autofocus' => true])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $service = app()->make(TranslationService::class);
        $targetLanguage = $service->getTargetLanguage();
        $group = request()->route('group');

        $data = $this->form->getState();

        // Save the current translation
        $service->setTranslation($targetLanguage, $data['translation_key'], $data['translation']);

        // Re-fetch missing translations
        $this->missingTranslations = $service->getMissingTranslations($targetLanguage, $group);

        // Check if there are more translations
        if (empty($this->missingTranslations)) {
            Notification::make()
                ->title('All translations complete')
                ->body('You have successfully translated all missing translations for this group.')
                ->success()
                ->send();

            $this->redirect(TranslationResource::getUrl('group', ['group' => $group]));

            return;
        }

        // Get the next missing translation
        $this->currentKey = array_key_first($this->missingTranslations);

        // Update the form with the next translation
        $this->form->fill([
            'translation_key' => $this->currentKey,
            'english' => $this->missingTranslations[$this->currentKey] ?? '',
            'translation' => '',
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save & Next')
                ->color(Color::Green)
                ->submit('save'),
        ];
    }

    public function getTitle(): string
    {
        return 'Quick Translate';
    }

    public function getBreadcrumbs(): array
    {
        return [
            'Translations',
            TranslationResource::getUrl('index') => 'Translations',
            TranslationResource::getUrl('group', ['group' => request()->route('group')]) => Str::headline(request()->route('group')),
            null => 'Quick Translate',
        ];
    }
}
