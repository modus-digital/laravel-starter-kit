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
use UnitEnum;

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
                Section::make('Logo')
                    ->description('Upload your brand logo to personalize your application.')
                    ->aside()
                    ->schema([
                        FileUpload::make('branding.logo')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('uploads')
                            ->acceptedFileTypes(['image/*', 'image/svg+xml'])
                            ->image()
                            ->maxSize(2048)
                            ->helperText('Upload your logo (max 2MB, supports SVG and common image formats)'),
                    ]),

                Section::make('Colors')
                    ->description('Choose your primary and secondary brand colors.')
                    ->aside()
                    ->schema([
                        ColorPicker::make('branding.primary_color')
                            ->label('Primary Color')
                            ->helperText('In this admin panel, the color will not perfectly match the chosen color. Due to the provider of this panel, the core application will use the correct colors.'),
                        ColorPicker::make('branding.secondary_color')
                            ->label('Secondary Color'),
                    ]),

                Section::make('Typography')
                    ->description('Select the font that best represents your brand.')
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
                ->label('Save')
                ->action(function () {
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
