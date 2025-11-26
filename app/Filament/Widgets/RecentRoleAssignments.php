<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\RBAC\Role;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class RecentRoleAssignments extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.widgets.recent_role_assignments.heading'))
            ->description(__('admin.widgets.recent_role_assignments.description'))
            ->query(
                User::query()
                    ->with('roles')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.widgets.recent_role_assignments.user'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                TextColumn::make('email')
                    ->label(__('admin.widgets.recent_role_assignments.email'))
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),
                TextColumn::make('roles.name')
                    ->label(__('admin.widgets.recent_role_assignments.roles'))
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(fn (string $state): string => Role::from($state)->getLabel())
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label(__('admin.widgets.recent_role_assignments.added'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn (User $record): string => $record->created_at?->format('M j, Y H:i') ?? ''),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
