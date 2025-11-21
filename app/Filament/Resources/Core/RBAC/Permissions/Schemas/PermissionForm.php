<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Permissions\Schemas;

use App\Enums\RBAC\Permission as RBACPermission;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

final class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.rbac.permissions.form.title'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('enum_key')
                            ->label(__('admin.rbac.permissions.form.enum_key'))
                            ->formatStateUsing(function (Permission $record): string {
                                $enumCases = collect(RBACPermission::cases())
                                    ->first(fn (RBACPermission $enum): bool => $enum->value === $record->name);

                                return $enumCases?->getLabel() ?? '-';
                            })
                            ->disabled(),

                        TextInput::make('name')
                            ->label(__('admin.rbac.permissions.form.name'))
                            ->disabled(),
                    ]),
            ]);
    }
}
