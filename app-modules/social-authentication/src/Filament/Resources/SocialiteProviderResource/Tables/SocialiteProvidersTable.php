<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\SocialiteProviderResource;

final class SocialiteProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->label(__('social-authentication::social-authentication.table.provider'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->getColor())
                    ->sortable(),

                IconColumn::make('is_enabled')
                    ->label(__('social-authentication::social-authentication.table.enabled'))
                    ->boolean()
                    ->sortable(),

                IconColumn::make('client_id')
                    ->label(__('social-authentication::social-authentication.table.configured'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->client_id))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('sort_order')
                    ->label(__('social-authentication::social-authentication.table.order'))
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('social-authentication::social-authentication.table.last_updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label(__('social-authentication::social-authentication.table.filters.status'))
                    ->placeholder(__('social-authentication::social-authentication.table.filters.status_all'))
                    ->trueLabel(__('social-authentication::social-authentication.table.filters.status_enabled'))
                    ->falseLabel(__('social-authentication::social-authentication.table.filters.status_disabled')),

                TernaryFilter::make('configured')
                    ->label(__('social-authentication::social-authentication.table.filters.configuration'))
                    ->placeholder(__('social-authentication::social-authentication.table.filters.configuration_all'))
                    ->trueLabel(__('social-authentication::social-authentication.table.filters.configuration_configured'))
                    ->falseLabel(__('social-authentication::social-authentication.table.filters.configuration_not_configured'))
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('client_id'),
                        false: fn ($query) => $query->whereNull('client_id'),
                    ),
            ])
            ->recordActions([
                Action::make('configure')
                    ->label(__('social-authentication::social-authentication.table.configure'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn ($record) => SocialiteProviderResource::getUrl('edit', ['record' => $record])),
            ])
            ->recordUrl(fn ($record) => SocialiteProviderResource::getUrl('edit', ['record' => $record]));
    }
}
