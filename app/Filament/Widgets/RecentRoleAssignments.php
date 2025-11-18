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
            ->heading('Recent User Role Assignments')
            ->description('Latest users and their assigned roles')
            ->query(
                User::query()
                    ->with('roles')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(fn (string $state): string => Role::from($state)->getLabel())
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn (User $record): string => $record->created_at->format('M j, Y H:i')),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
