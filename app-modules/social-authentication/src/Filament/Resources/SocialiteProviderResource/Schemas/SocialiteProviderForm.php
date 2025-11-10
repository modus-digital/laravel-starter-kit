<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use ModusDigital\SocialAuthentication\Enums\AuthenticationProvider;

final class SocialiteProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('social-authentication::social-authentication.form.provider_details.title'))
                    ->description(__('social-authentication::social-authentication.form.provider_details.description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('social-authentication::social-authentication.form.provider_details.name'))
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('provider')
                                    ->label(__('social-authentication::social-authentication.form.provider_details.provider_type'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($state) => $state instanceof AuthenticationProvider ? $state->getLabel() : $state),
                            ]),
                    ]),

                Section::make(__('social-authentication::social-authentication.form.socialite_configuration.title'))
                    ->description(__('social-authentication::social-authentication.form.socialite_configuration.description'))
                    ->schema([
                        TextInput::make('client_id')
                            ->label(__('social-authentication::social-authentication.form.socialite_configuration.client_id'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('social-authentication::social-authentication.form.socialite_configuration.client_id_helper'))
                            ->columnSpanFull(),

                        TextInput::make('client_secret')
                            ->label(__('social-authentication::social-authentication.form.socialite_configuration.client_secret'))
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText(__('social-authentication::social-authentication.form.socialite_configuration.client_secret_helper'))
                            ->columnSpanFull(),

                        TextInput::make('redirect_uri')
                            ->label(__('social-authentication::social-authentication.form.socialite_configuration.redirect_uri'))
                            ->disabled()
                            ->formatStateUsing(function ($record) {
                                if (! $record) {
                                    return '';
                                }

                                $provider = $record->provider instanceof AuthenticationProvider
                                    ? $record->provider->value
                                    : $record->provider;

                                $domain = mb_rtrim(config('app.url'), '/');

                                return "{$domain}/auth/{$provider}/callback";
                            })
                            ->helperText(__('social-authentication::social-authentication.form.socialite_configuration.redirect_uri_helper'))
                            ->columnSpanFull()
                            ->copyable(),
                    ]),

                Section::make(__('social-authentication::social-authentication.form.settings.title'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('social-authentication::social-authentication.form.settings.enable_provider'))
                                    ->helperText(__('social-authentication::social-authentication.form.settings.enable_provider_helper'))
                                    ->default(false),

                                TextInput::make('sort_order')
                                    ->label(__('social-authentication::social-authentication.form.settings.display_order'))
                                    ->numeric()
                                    ->helperText(__('social-authentication::social-authentication.form.settings.display_order_helper'))
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }
}
