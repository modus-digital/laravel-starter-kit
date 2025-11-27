<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\Tables;

use App\Enums\ActivityStatus;
use App\Models\Modules\Clients\Client;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.clients.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('contact_name')
                    ->label(__('admin.clients.table.contact_name'))
                    ->description(function (Client $record): string {
                        if ($record->contact_email && $record->contact_phone) {
                            return "$record->contact_email ($record->contact_phone)";
                        }

                        if ($record->contact_email) {
                            return $record->contact_email;
                        }

                        if ($record->contact_phone) {
                            return $record->contact_phone;
                        }

                        return '';
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('admin.clients.table.status'))
                    ->badge()
                    ->color(function (?Client $record): string {
                        if (! $record instanceof Client) {
                            return 'gray';
                        }

                        return match ($record->status) {
                            ActivityStatus::ACTIVE->value => 'success',
                            ActivityStatus::INACTIVE->value => 'danger',
                            ActivityStatus::SUSPENDED->value => 'warning',
                            ActivityStatus::DELETED->value => 'danger',
                            default => 'gray',
                        };
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('admin.clients.table.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
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
