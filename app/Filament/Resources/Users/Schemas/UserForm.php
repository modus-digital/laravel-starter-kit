<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\ActivityStatus;
use App\Filament\CustomFields\RoleSelect;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.users.form.personal_information.title'))
                    ->description(__('admin.users.form.personal_information.description'))
                    ->columns(5)
                    ->aside()
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->label(__('admin.users.form.personal_information.avatar'))
                            ->image()
                            ->imageEditor()
                            ->avatar(),

                        Grid::make()
                            ->columnSpan(4)
                            ->columns(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.users.form.personal_information.name'))
                                    ->required(),
                                TextInput::make('email')
                                    ->label(__('admin.users.form.personal_information.email'))
                                    ->required(),
                            ]),
                    ]),

                Section::make(__('admin.users.form.security.title'))
                    ->description(__('admin.users.form.security.description'))
                    ->columns(2)
                    ->aside()
                    ->schema([
                        RoleSelect::make('role')
                            ->label(__('admin.users.form.security.role'))
                            ->columnSpan(1)
                            ->required(),

                        Select::make('status')
                            ->label(__('admin.users.form.security.status'))
                            ->columnSpan(1)
                            ->native(false)
                            ->options(ActivityStatus::options())
                            ->required(),
                    ]),
            ]);
    }
}
