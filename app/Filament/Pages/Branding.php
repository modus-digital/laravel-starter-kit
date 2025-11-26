<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\BrandingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use JaOcero\RadioDeck\Forms\Components\RadioDeck;
use Outerweb\FilamentSettings\Pages\Settings;
use Spatie\Activitylog\Facades\Activity;

final class Branding extends Settings
{
    protected string $view = 'filament.pages.branding';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'system/branding';

    protected ?Alignment $headerActionsAlignment = Alignment::End;

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.branding');
    }

    public function form(Schema $schema): Schema
    {
        return $this->defaultForm($schema)
            ->columns(1)
            ->components([
                Section::make(__('admin.branding.sections.logo'))
                    ->description(__('admin.branding.descriptions.logo'))
                    ->aside()
                    ->schema([
                        FileUpload::make('branding.logo')
                            ->label(__('admin.branding.labels.logo'))
                            ->disk('public')
                            ->directory('uploads')
                            ->acceptedFileTypes(['image/*', 'image/svg+xml'])
                            ->image()
                            ->maxSize(2048)
                            ->helperText(__('admin.branding.helpers.logo')),
                    ]),

                Section::make(__('admin.branding.sections.colors'))
                    ->description(__('admin.branding.descriptions.colors'))
                    ->aside()
                    ->schema([
                        ColorPicker::make('branding.primary_color')
                            ->label(__('admin.branding.labels.primary_color'))
                            ->helperText(__('admin.branding.helpers.primary_color')),
                        ColorPicker::make('branding.secondary_color')
                            ->label(__('admin.branding.labels.secondary_color')),
                    ]),

                Section::make(__('admin.branding.sections.typography'))
                    ->description(__('admin.branding.descriptions.typography'))
                    ->aside()
                    ->schema([
                        RadioDeck::make('branding.font')
                            ->options([
                                'inter' => 'Inter',
                                'roboto' => 'Roboto',
                                'poppins' => 'Poppins',
                                'lato' => 'Lato',
                                'inria_serif' => 'Inria Serif',
                                'arvo' => 'Arvo',
                            ])
                            ->descriptions([
                                'inter' => 'Modern sans-serif',
                                'roboto' => 'Google\'s signature font',
                                'poppins' => 'Geometric sans-serif',
                                'lato' => 'Elegant and warm',
                                'inria_serif' => 'Contemporary serif',
                                'arvo' => 'Classic and readable',
                            ])
                            ->icons([
                                'inter' => 'heroicon-m-language',
                                'roboto' => 'heroicon-m-document-text',
                                'poppins' => 'heroicon-m-sparkles',
                                'lato' => 'heroicon-m-pencil-square',
                                'inria_serif' => 'heroicon-m-book-open',
                                'arvo' => 'heroicon-m-newspaper',
                            ])
                            ->iconSize(IconSize::Medium)
                            ->iconPosition(IconPosition::Before)
                            ->alignment(Alignment::Center)
                            ->padding('px-4 py-6')
                            ->extraCardsAttributes([
                                'class' => 'rounded-xl',
                            ])
                            ->extraOptionsAttributes([
                                'class' => 'text-lg leading-none w-full flex flex-col items-center justify-center',
                            ])
                            ->extraDescriptionsAttributes([
                                'class' => 'text-sm font-light text-center',
                            ])
                            ->colors('primary')
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function getFormActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('admin.branding.labels.save'))
                ->action(function (): void {
                    $this->save();

                    Activity::inLog('administration')
                        ->event('branding.updated')
                        ->causedBy(Auth::user())
                        ->log('');

                    app(BrandingService::class)->clearCache();
                    $this->js('window.location.reload()');
                }),
        ];
    }
}
