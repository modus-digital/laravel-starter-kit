<?php

namespace App\Filament\Resources\Core\Activities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
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
                    ->formatStateUsing(function ($state, $record) {
                        return __($state, [
                            'issuer' => $record->causer?->name ?? '',
                            'target' => $record->subject?->name ?? '',
                            'email' => $record->properties['credentials']['email'] ?? '',
                        ]);
                    })
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
