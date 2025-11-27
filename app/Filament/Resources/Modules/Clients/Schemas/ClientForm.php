<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\Schemas;

use App\Enums\ActivityStatus;
use CountryEnums\Country;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.clients.form.base.title'))
                    ->description(__('admin.clients.form.base.description'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.clients.form.base.name'))
                            ->unique(ignoreRecord: true)
                            ->required(),

                        Toggle::make('status')
                            ->label(__('admin.clients.form.base.status'))
                            ->afterStateHydrated(
                                function (Toggle $component, ActivityStatus|string|null $state): void {
                                    $enumState = match (true) {
                                        $state === null, $state === '' => null,
                                        $state instanceof ActivityStatus => $state,
                                        ctype_digit($state) => ((int) $state) === 1
                                            ? ActivityStatus::ACTIVE
                                            : ActivityStatus::INACTIVE,
                                        default => ActivityStatus::from($state),
                                    };

                                    $component->state(
                                        $enumState === null || $enumState === ActivityStatus::ACTIVE,
                                    );
                                },
                            )
                            ->dehydrateStateUsing(
                                fn (bool $state): ActivityStatus => $state
                                    ? ActivityStatus::ACTIVE
                                    : ActivityStatus::INACTIVE,
                            )
                            ->inline(false)
                            ->required(),
                    ]),

                Section::make(__('admin.clients.form.contact_information.title'))
                    ->description(__('admin.clients.form.contact_information.description'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_name')
                            ->label(__('admin.clients.form.contact_information.contact_name'))
                            ->columnSpanFull(),

                        TextInput::make('contact_email')
                            ->label(__('admin.clients.form.contact_information.contact_email'))
                            ->email(),

                        TextInput::make('contact_phone')
                            ->label(__('admin.clients.form.contact_information.contact_phone'))
                            ->tel(),
                    ]),

                Section::make(__('admin.clients.form.location.title'))
                    ->description(__('admin.clients.form.location.description'))
                    ->aside()
                    ->columns(4)
                    ->schema([
                        TextInput::make('address')
                            ->label(__('admin.clients.form.location.address'))
                            ->columnSpan(3),

                        TextInput::make('postal_code')
                            ->label(__('admin.clients.form.location.postal_code'))
                            ->columnSpan(1),

                        TextInput::make('city')
                            ->label(__('admin.clients.form.location.city'))
                            ->columnSpan(2),

                        Select::make('country')
                            ->label(__('admin.clients.form.location.country'))
                            ->columnSpan(2)
                            ->options(Country::getOptions())
                            ->searchable()
                            ->position('top')
                            ->native(false),
                    ]),

            ]);
    }
}
