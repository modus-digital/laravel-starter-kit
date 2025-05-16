<?php

namespace App\Filament\Pages;

use Closure;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{
    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tab::make('General')
                        ->schema([
                            TextInput::make('general.app_name')->required(),
                            FileUpload::make('general.logo'),
                        ]),

                    Tab::make('Features')
                        ->schema([
                            Fieldset::make('features.auth')
                                ->label('Authentication')
                                ->schema([
                                    Toggle::make('features.auth.register'),
                                    Toggle::make('features.auth.login'),
                                    Toggle::make('features.auth.two_factor_authentication'),
                                    Toggle::make('features.auth.password_reset'),
                                    Toggle::make('features.auth.email_verification'),
                                ])
                                ->columns(3),

                            Fieldset::make('tools')
                                ->label('Tools')
                                ->schema([
                                    Toggle::make('features.tools.translation_manager'),
                                ])
                                ->columns(3),
                        ]),
                ])
        ];
    }
}
