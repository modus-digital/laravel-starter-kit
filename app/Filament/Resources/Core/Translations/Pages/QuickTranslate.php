<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations\Pages;

use App\Enums\Language;
use App\Filament\Resources\Core\Translations\TranslationResource;
use App\Filament\Resources\Core\Translations\TranslationService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class QuickTranslate extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array<string, string> */
    public array $missingTranslations = [];

    public ?string $currentKey = null;

    protected static string $resource = TranslationResource::class;

    protected string $view = 'filament.resources.core.translations.pages.quick-translate';

    public function mount(): void
    {
        $service = app()->make(TranslationService::class);
        $targetLanguage = $service->getTargetLanguage();
        $group = request()->route('group');

        $this->missingTranslations = $service->getMissingTranslations($targetLanguage, $group);

        if ($this->missingTranslations === []) {
            Notification::make()
                ->title(__('admin.translations.notifications.all_translations_complete.title'))
                ->body(__('admin.translations.notifications.all_translations_complete.body'))
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
                Section::make(__('admin.translations.quick_translate.base'))
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
        if ($this->missingTranslations === []) {
            Notification::make()
                ->title(__('admin.translations.notifications.all_translations_complete_group.title'))
                ->body(__('admin.translations.notifications.all_translations_complete_group.body'))
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

    public function getTitle(): string
    {
        return __('admin.translations.quick_translate.title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('admin.translations.quick_translate.breadcrumbs.translations'),
            TranslationResource::getUrl('index') => __('admin.translations.quick_translate.breadcrumbs.translations'),
            TranslationResource::getUrl('group', ['group' => request()->route('group')]) => Str::headline(request()->route('group')),
            null => __('admin.translations.quick_translate.title'),
        ];
    }
}
