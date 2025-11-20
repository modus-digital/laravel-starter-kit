<?php

namespace App\Filament\Resources\Core\RBAC\Roles\Schemas;

use App\Enums\RBAC\Role as RBACRole;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

final class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.rbac.roles.form.title'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('enum_key')
                            ->label(__('admin.rbac.roles.form.enum_key'))
                            ->formatStateUsing(fn (Role $record): string => RBACRole::from($record->name)->getLabel())
                            ->disabled(),

                        TextInput::make('name')
                            ->label(__('admin.rbac.roles.form.name'))
                            ->disabled(),
                    ]),
            ]);
    }
}
