<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders\Schemas;

use App\Enums\AuthenticationProvider;
use App\Models\Modules\SocialiteProvider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SocialiteProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.socialite_providers.form.provider_details.title'))
                    ->description(__('admin.socialite_providers.form.provider_details.description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.socialite_providers.form.provider_details.name'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn (SocialiteProvider $record): string => AuthenticationProvider::from($record->name)->getLabel())
                                    ->helperText(__('admin.socialite_providers.form.provider_details.name_helper')),

                                Toggle::make('is_enabled')
                                    ->label(__('admin.socialite_providers.form.provider_details.is_enabled'))
                                    ->helperText(__('admin.socialite_providers.form.provider_details.is_enabled_helper'))
                                    ->formatStateUsing(fn (SocialiteProvider $record): bool => $record->is_enabled)
                                    ->inline(false)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),

                Section::make(__('admin.socialite_providers.form.socialite_configuration.title'))
                    ->description(__('admin.socialite_providers.form.socialite_configuration.description'))
                    ->schema([
                        TextInput::make('client_id')
                            ->label(__('admin.socialite_providers.form.socialite_configuration.client_id'))
                            ->maxLength(255)
                            ->helperText(__('admin.socialite_providers.form.socialite_configuration.client_id_helper'))
                            ->columnSpanFull(),

                        TextInput::make('client_secret')
                            ->label(__('admin.socialite_providers.form.socialite_configuration.client_secret'))
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText(__('admin.socialite_providers.form.socialite_configuration.client_secret_helper'))
                            ->columnSpanFull(),

                        TextInput::make('redirect_uri')
                            ->label(__('admin.socialite_providers.form.socialite_configuration.redirect_uri'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function (?SocialiteProvider $record) {
                                if (! $record) {
                                    return '';
                                }

                                $provider = $record->name instanceof AuthenticationProvider
                                    ? $record->name->value
                                    : $record->name;

                                $domain = mb_rtrim(config('app.url'), '/');

                                return "{$domain}/auth/{$provider}/callback";
                            })
                            ->helperText(__('admin.socialite_providers.form.socialite_configuration.redirect_uri_helper'))
                            ->columnSpanFull()
                            ->copyable(),
                    ]),
            ]);
    }
}
