<?php

namespace App\Filament\Resources\Core\Users\Schemas;

use App\Enums\ActivityStatus;
use App\Filament\Overrides\RoleSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.users.form.personal_information.title'))
                    ->description(__('admin.users.form.personal_information.description'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->label(__('admin.users.form.name'))
                                    ->required(),

                                TextInput::make('email')
                                    ->label(__('admin.users.form.email'))
                                    ->columns(1)
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                TextInput::make('phone')
                                    ->label(__('admin.users.form.phone'))
                                    ->columns(1)
                                    ->tel()
                                    ->required(),
                            ]),
                    ]),

                Section::make(__('admin.users.form.security.title'))
                    ->description(__('admin.users.form.security.description'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        RoleSelect::make('role')
                            ->label(__('admin.users.form.role'))
                            ->columnSpan(1)
                            ->required(),

                        Select::make('status')
                            ->label(__('admin.users.form.status'))
                            ->columnSpan(1)
                            ->native(false)
                            ->options(ActivityStatus::options())
                            ->required(),
                    ]),
            ]);
    }
}
