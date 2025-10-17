<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Filament\Resources\ClientResource\Tables;

use App\Enums\ActivityStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use ModusDigital\Clients\Models\Client;

final class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('clients::clients.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('website')
                    ->label(__('clients::clients.table.website'))
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('clients::clients.table.status'))
                    ->getStateUsing(fn (?Client $record): ?string => $record?->status?->getLabel())
                    ->icon(fn (?Client $record) => $record?->status === ActivityStatus::ACTIVE ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (?Client $record) => $record?->status === ActivityStatus::ACTIVE ? 'success' : 'danger')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('clients::clients.table.created_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('clients::clients.table.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label(__('clients::clients.table.filters'))
            )
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
