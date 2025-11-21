<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders\Tables;

use App\Enums\AuthenticationProvider;
use App\Models\Modules\SocialiteProvider;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class SocialiteProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderBy('sort_order', 'asc'))
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->label($isReordering ? __('admin.socialite_providers.table.disable_reorder') : __('admin.socialite_providers.table.enable_reorder'))
                    ->color('gray')
                    ->tooltip(__('admin.socialite_providers.table.reorder_tooltip'))
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.socialite_providers.table.name'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (SocialiteProvider $record): string => AuthenticationProvider::from($record->name)->getLabel()),

                IconColumn::make('is_enabled')
                    ->label(__('admin.socialite_providers.table.is_enabled'))
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (SocialiteProvider $record): bool => config("modules.socialite.providers.{$record->name}", false)),
                ViewAction::make(),
            ]);
    }
}
