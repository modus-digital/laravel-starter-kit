<?php

namespace App\Filament\Resources\Core\Users\Tables;

use App\Enums\ActivityStatus;
use App\Enums\AuthenticationProvider;
use App\Enums\RBAC\Role;
use App\Filament\Overrides\ImpersonateAction;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.users.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('admin.users.table.email'))
                    ->sortable()
                    ->searchable(),

                ...(
                    config(key: 'modules.socialite.enabled', default: false)
                        ? [TextColumn::make('provider')
                            ->label(__('admin.users.table.auth_provider'))
                            ->icon(fn (?User $record) => AuthenticationProvider::from($record?->provider)->getIcon())
                            ->color(fn (?User $record) => AuthenticationProvider::from($record?->provider)->getColor())
                            ->badge()
                            ->sortable()
                            ->searchable()]
                        : []
                ),

                TextColumn::make('role')
                    ->label(__('admin.users.table.role'))
                    ->getStateUsing(fn (?User $record): string => Role::from($record?->roles->first()?->name)->getLabel() ?? __('admin.users.table.no_role'))
                    ->icon(fn (?User $record) => Role::from($record?->roles->first()?->name)->getIcon())
                    ->color(fn (?User $record) => Role::from($record?->roles->first()?->name)->getFilamentColor())
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('admin.users.table.status'))
                    ->getStateUsing(fn (?User $record): string => ActivityStatus::from($record?->status)->getLabel())
                    ->color(fn (?User $record): string => ActivityStatus::from($record?->status)->getColor())
                    ->badge()
                    ->sortable()
                    ->searchable(),

                IconColumn::make('two_factor_secret')
                    ->label(__('admin.users.table.two_factor'))
                    ->tooltip(fn (?User $record): string => ! empty($record?->two_factor_secret) ? __('admin.users.table.two_factor_enabled') : __('admin.users.table.two_factor_disabled'))
                    ->getStateUsing(fn (?User $record): bool => ! empty($record?->two_factor_secret))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label(__('admin.users.table.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ImpersonateAction::make(),
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
