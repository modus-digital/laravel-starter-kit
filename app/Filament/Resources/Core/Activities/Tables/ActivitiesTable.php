<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Activities\Tables;

use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::getQuery())
            ->columns([
                TextColumn::make('description')
                    ->label(__('admin.activities.table.description'))
                    ->formatStateUsing(fn (Activity $record): string => $record->getTranslatedDescription())
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('event')
                    ->label(__('admin.activities.table.event'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label(__('admin.activities.table.causer'))
                    ->formatStateUsing(fn (?string $state, Activity $record): string => $record->causer->name ?? $record->causer->email ?? 'System')
                    ->searchable()
                    ->sortable()
                    ->default('System'),

                TextColumn::make('subject')
                    ->label(__('admin.activities.table.subject'))
                    ->formatStateUsing(function (Activity $record): string {
                        if (! $record->subject) {
                            return '-';
                        }

                        $subjectName = class_basename($record->subject_type ?? '');

                        // Try to get a name property from the subject if it exists
                        if (method_exists($record->subject, 'getNameAttribute') || isset($record->subject->name)) {
                            $name = $record->subject->name ?? $record->subject->email ?? null;
                            if ($name) {
                                return "{$subjectName}: {$name}";
                            }
                        }

                        return "{$subjectName} ({$record->subject_id})";
                    })
                    ->searchable(query: fn ($query, string $search) => $query->whereHasMorph('subject', '*', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }))
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('subject_type', $direction)
                        ->orderBy('subject_id', $direction))
                    ->default('-'),

                TextColumn::make('created_at')
                    ->label(__('admin.activities.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('common.actions.view'))
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->modalHeading(__('admin.activities.modal.heading'))
                    ->modalWidth('4xl')
                    ->slideOver()
                    ->modalContent(fn (Activity $record): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view('filament.resources.activities.activity-details', [
                        'activity' => $record,
                    ])),
            ])
            ->recordAction('view');
    }

    private static function getQuery(): Builder
    {
        return Activity::whereNotIn('log_name', config('modules.activity_logs.banlist', []))
            ->latest();
    }
}
