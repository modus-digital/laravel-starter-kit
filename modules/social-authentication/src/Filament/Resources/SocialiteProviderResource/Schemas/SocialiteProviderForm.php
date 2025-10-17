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
                Section::make('Provider Details')
                    ->description('Provider type and name are pre-configured and cannot be changed')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Provider Name')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('provider')
                                    ->label('Provider Type')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($state) => $state instanceof AuthenticationProvider ? $state->getLabel() : $state),
                            ]),
                    ]),

                Section::make('Socialite Configuration')
                    ->description('Configure your OAuth credentials from the provider')
                    ->schema([
                        TextInput::make('client_id')
                            ->label('Client ID')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Your OAuth application client ID')
                            ->columnSpanFull(),

                        TextInput::make('client_secret')
                            ->label('Client Secret')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Stored encrypted in the database')
                            ->columnSpanFull(),

                        TextInput::make('redirect_uri')
                            ->label('Redirect URI')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->helperText('The callback URL (e.g., https://yourdomain.com/auth/{provider}/callback)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label('Enable Provider')
                                    ->helperText('When enabled, users can authenticate with this provider')
                                    ->default(false),

                                TextInput::make('sort_order')
                                    ->label('Display Order')
                                    ->numeric()
                                    ->helperText('Controls the button order on the login page')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }
}
