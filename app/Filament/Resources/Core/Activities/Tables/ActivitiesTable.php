<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Activities\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label(__('admin.activities.table.log_name'))
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('admin.activities.table.description'))
                    ->limit(50)
                    ->formatStateUsing(fn ($state, $record): string|array|null => __($state, [
                        'issuer' => $record->causer?->name ?? '',
                        'target' => $record->subject?->name ?? '',
                        'email' => $record->properties['credentials']['email'] ?? '',
                    ]))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label(__('admin.activities.table.causer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label(__('admin.activities.table.subject'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.activities.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ]);
    }
}
