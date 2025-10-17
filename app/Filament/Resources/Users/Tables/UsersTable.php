<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Vite;
use ModusDigital\SocialAuthentication\Enums\AuthenticationProvider;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label(__('admin.users.table.avatar'))
                    ->defaultImageUrl(Vite::asset('resources/images/default-avatar.jpg'))
                    ->circular(),

                TextColumn::make('name')
                    ->label(__('admin.users.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('admin.users.table.email'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('provider')
                    ->label(__('admin.users.table.provider'))
                    ->getStateUsing(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record?->provider)->getLabel() : 'Email')
                    ->icon(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record?->provider)->getIcon() : 'heroicon-o-envelope')
                    ->color(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record?->provider)->getColor() : 'primary')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('role')
                    ->label(__('admin.users.table.role'))
                    ->getStateUsing(fn (?User $record): string => Role::from($record?->roles->first()?->name)->getLabel() ?? 'No role')
                    ->icon(fn (?User $record) => Role::from($record?->roles->first()?->name)->getIcon())
                    ->color(fn (?User $record) => Role::from($record?->roles->first()?->name)->getFilamentColor())
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('admin.users.table.status'))
                    ->getStateUsing(fn (?User $record) => $record?->status->getLabel())
                    ->icon(fn (?User $record) => $record?->status === ActivityStatus::ACTIVE ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (?User $record) => $record?->status === ActivityStatus::ACTIVE ? 'success' : 'danger')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('admin.users.table.created_at'))
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('admin.users.table.updated_at'))
                    ->sortable(),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label(__('admin.users.table.filters'))
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
