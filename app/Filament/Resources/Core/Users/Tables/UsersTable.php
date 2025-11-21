<?php

declare(strict_types=1);

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

final class UsersTable
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
                            ->icon(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record->provider)->getIcon() : null)
                            ->color(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record->provider)->getColor() : null)
                            ->badge()
                            ->sortable()
                            ->searchable()]
                        : []
                ),

                TextColumn::make('role')
                    ->label(__('admin.users.table.role'))
                    ->getStateUsing(function (?User $record): string {
                        if (! $record instanceof User) {
                            return __('admin.users.table.no_role');
                        }
                        /** @var \Spatie\Permission\Models\Role|null $firstRole */
                        $firstRole = $record->roles->first();
                        if (! $firstRole) {
                            return __('admin.users.table.no_role');
                        }

                        return Role::from($firstRole->name)->getLabel();
                    })
                    ->icon(function (?User $record) {
                        if (! $record instanceof User) {
                            return null;
                        }
                        /** @var \Spatie\Permission\Models\Role|null $firstRole */
                        $firstRole = $record->roles->first();
                        if (! $firstRole) {
                            return null;
                        }

                        return Role::from($firstRole->name)->getIcon();
                    })
                    ->color(function (?User $record) {
                        if (! $record instanceof User) {
                            return null;
                        }
                        /** @var \Spatie\Permission\Models\Role|null $firstRole */
                        $firstRole = $record->roles->first();
                        if (! $firstRole) {
                            return null;
                        }

                        return Role::from($firstRole->name)->getFilamentColor();
                    })
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('admin.users.table.status'))
                    ->getStateUsing(fn (?User $record): string => $record?->status->getLabel() ?? '')
                    ->color(function (?User $record): string {
                        if (! $record instanceof User) {
                            return 'gray';
                        }

                        return match ($record->status) {
                            ActivityStatus::ACTIVE => 'success',
                            ActivityStatus::INACTIVE => 'danger',
                            ActivityStatus::SUSPENDED => 'warning',
                            ActivityStatus::DELETED => 'danger',
                        };
                    })
                    ->badge()
                    ->sortable()
                    ->searchable(),

                IconColumn::make('two_factor_secret')
                    ->label(__('admin.users.table.two_factor'))
                    ->tooltip(fn (?User $record): string => empty($record->two_factor_secret) ? __('admin.users.table.two_factor_disabled') : __('admin.users.table.two_factor_enabled'))
                    ->getStateUsing(fn (?User $record): bool => ! empty($record->two_factor_secret))
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
