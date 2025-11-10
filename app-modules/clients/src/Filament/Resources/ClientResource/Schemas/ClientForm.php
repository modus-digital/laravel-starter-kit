<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Filament\Resources\ClientResource\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('clients::clients.form.information.title'))
                    ->description(__('clients::clients.form.information.description'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('clients::clients.form.information.name'))
                            ->required(),

                        TextInput::make('website')
                            ->label(__('clients::clients.form.information.website'))
                            ->url(),

                        Grid::make()
                            ->columns(1)
                            ->columnSpan(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('clients::clients.form.information.status'))
                                    ->native(false)
                                    ->options(ActivityStatus::options())
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
