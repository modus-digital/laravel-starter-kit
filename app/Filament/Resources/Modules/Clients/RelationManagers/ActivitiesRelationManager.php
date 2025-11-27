<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\RelationManagers;

use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->activities()->exists();
    }

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.activities.navigation_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->label(__('admin.activities.table.description'))
                    ->formatStateUsing(fn (Activity $record): string => $record->getTranslatedDescription())
                    ->sortable()
                    ->wrap(),

                TextColumn::make('event')
                    ->label(__('admin.activities.table.event'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label(__('admin.activities.table.causer'))
                    ->formatStateUsing(
                        fn (?string $state, Activity $record): string => $record->causer?->name
                            ?? $record->causer?->email
                            ?? 'System',
                    )
                    ->sortable()
                    ->default('System'),

                TextColumn::make('created_at')
                    ->label(__('admin.activities.table.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('view')
                    ->label(__('common.actions.view'))
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->modalHeading(__('admin.activities.modal.heading'))
                    ->modalWidth('4xl')
                    ->slideOver()
                    ->modalContent(
                        fn (Activity $record) => view(
                            'filament.resources.activities.activity-details',
                            [
                                'activity' => $record,
                            ],
                        ),
                    ),
            ])
            ->recordAction('view')
            ->headerActions([])
            ->toolbarActions([]);
    }
}


