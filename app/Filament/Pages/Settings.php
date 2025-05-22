<?php

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use Override;
use Closure;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Permission::CAN_ACCESS_SETTINGS);
    }

    #[Override]
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
                                    Toggle::make('features.auth.register')->label('Register'),
                                    Toggle::make('features.auth.login')->label('Login'),
                                    Toggle::make('features.auth.password_reset')->label('Password Reset'),
                                    Toggle::make('features.auth.email_verification')->label('Email Verification'),
                                ])
                                ->columns(2),
                        ]),
                ])
        ];
    }
}
