<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use Closure;
use Filament\Forms\Components\{Fieldset, FileUpload, Tabs, TextInput, Toggle};
use Filament\Forms\Components\Tabs\Tab;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use Override;

/**
 * Settings page for managing application configuration.
 *
 * This page provides interfaces for:
 * - General application settings (name, logo)
 * - Feature toggles (authentication options)
 * - Application configuration management
 *
 * @since 1.0.0
 */
class Settings extends BaseSettings
{
    /**
     * Determine if the current user can access the settings page.
     *
     * @return bool True if the user has permission to access settings
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Permission::CAN_ACCESS_SETTINGS);
    }

    /**
     * Get the navigation group this page belongs to.
     *
     * @return string|null The navigation group name
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.applicatie-info');
    }

    /**
     * Define the schema for the settings form.
     *
     * @return array|Closure The form schema
     */
    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tab::make('General')
                        ->schema([
                            TextInput::make('general.app_name')
                                ->required(),
                            FileUpload::make('general.logo'),
                        ]),

                    Tab::make('Features')
                        ->schema([
                            Fieldset::make('features.auth')
                                ->label('Authentication')
                                ->schema([
                                    Toggle::make('features.auth.register')
                                        ->label('Register'),
                                    Toggle::make('features.auth.login')
                                        ->label('Login'),
                                    Toggle::make('features.auth.password_reset')
                                        ->label('Password Reset'),
                                    Toggle::make('features.auth.email_verification')
                                        ->label('Email Verification'),
                                ])
                                ->columns(2),
                        ]),
                ])
        ];
    }
}
