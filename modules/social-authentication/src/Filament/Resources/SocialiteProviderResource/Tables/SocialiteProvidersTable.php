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
                    ->label('Provider')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->getColor())
                    ->sortable(),

                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('client_id')
                    ->label('Configured')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->client_id))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label('Status')
                    ->placeholder('All providers')
                    ->trueLabel('Enabled only')
                    ->falseLabel('Disabled only'),

                TernaryFilter::make('configured')
                    ->label('Configuration')
                    ->placeholder('All providers')
                    ->trueLabel('Configured only')
                    ->falseLabel('Not configured')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('client_id'),
                        false: fn ($query) => $query->whereNull('client_id'),
                    ),
            ])
            ->recordActions([
                Action::make('configure')
                    ->label('Configure')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn ($record) => SocialiteProviderResource::getUrl('edit', ['record' => $record])),
            ])
            ->recordUrl(fn ($record) => SocialiteProviderResource::getUrl('edit', ['record' => $record]));
    }
}
