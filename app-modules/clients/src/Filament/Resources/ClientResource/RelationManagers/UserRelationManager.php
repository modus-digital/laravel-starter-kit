<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use ModusDigital\Clients\Models\Client;

final class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('clients::clients.relation_managers.user.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('clients::clients.relation_managers.user.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('clients::clients.relation_managers.user.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('clients::clients.relation_managers.user.status'))
                    ->getStateUsing(fn (?User $record) => $record?->status->getLabel())
                    ->icon(fn (?User $record) => $record?->status === ActivityStatus::ACTIVE ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (?User $record) => $record?->status === ActivityStatus::ACTIVE ? 'success' : 'danger')
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('attachUser')
                    ->label(__('clients::clients.relation_managers.user.attach_user'))
                    ->modalHeading(__('clients::clients.relation_managers.user.attach_user_modal_heading'))
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('primary')
                    ->schema([
                        Select::make('users')
                            ->label(__('clients::clients.relation_managers.user.users'))
                            ->options(function () {
                                /** @var Client $client */
                                $client = $this->getOwnerRecord();
                                $existingUsers = $client->users()->pluck('users.id')->toArray();

                                return User::whereNotIn('id', $existingUsers)
                                    ->orderBy('name')
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(__('clients::clients.relation_managers.user.users_placeholder'))
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        if (empty($data['users'])) {
                            return;
                        }

                        /** @var Client $client */
                        $client = $this->getOwnerRecord();
                        $client->users()->attach($data['users']);

                        Notification::make()
                            ->title(__('clients::clients.relation_managers.user.users_attached', ['count' => count($data['users'])]))
                            ->success()
                            ->send();
                    }),

                Action::make('createUser')
                    ->label(__('clients::clients.relation_managers.user.create_user'))
                    ->modalHeading(__('clients::clients.relation_managers.user.create_user_modal_heading'))
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('success')
                    ->schema([
                        Section::make(__('clients::clients.relation_managers.user.personal_information'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('clients::clients.relation_managers.user.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label(__('clients::clients.relation_managers.user.email'))
                                    ->email()
                                    ->required()
                                    ->unique(User::class, 'email')
                                    ->maxLength(255),
                            ]),

                        Section::make(__('clients::clients.relation_managers.user.security'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('clients::clients.relation_managers.user.password'))
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->maxLength(255),

                                Select::make('role')
                                    ->label(__('clients::clients.relation_managers.user.role'))
                                    ->options(Role::options())
                                    ->native(false)
                                    ->required(),

                                Grid::make()
                                    ->columns(1)
                                    ->columnSpan(2)
                                    ->schema([
                                        Select::make('status')
                                            ->label(__('clients::clients.relation_managers.user.status'))
                                            ->options(ActivityStatus::options())
                                            ->default(ActivityStatus::ACTIVE->value)
                                            ->native(false)
                                            ->required(),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        /** @var Client $client */
                        $client = $this->getOwnerRecord();

                        // Create the new user
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => $data['password'],
                            'status' => $data['status'],
                        ]);

                        // Assign role to the user
                        $user->assignRole($data['role']);

                        // Attach the user to the client
                        $client->users()->attach($user->id);

                        Notification::make()
                            ->title(__('clients::clients.relation_managers.user.user_created', ['name' => $user->name]))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('detachUser')
                    ->label(__('clients::clients.relation_managers.user.detach_user'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('clients::clients.relation_managers.user.detach_user_modal_heading'))
                    ->modalDescription(__('clients::clients.relation_managers.user.detach_user_modal_description'))
                    ->action(function (User $record): void {
                        /** @var Client $client */
                        $client = $this->getOwnerRecord();

                        // Detach the user from the client
                        $client->users()->detach($record->id);

                        Notification::make()
                            ->title(__('clients::clients.relation_managers.user.user_detached', ['name' => $record->name]))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
