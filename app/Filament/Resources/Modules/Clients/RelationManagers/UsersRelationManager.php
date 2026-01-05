<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients\RelationManagers;

use App\Enums\ActivityStatus;
use App\Enums\AuthenticationProvider;
use App\Enums\RBAC\Role;
use App\Filament\Overrides\ImpersonateAction;
use App\Filament\Overrides\RoleSelect;
use App\Models\Modules\Clients\Client;
use App\Models\User;
use App\Notifications\Auth\AccountCreated;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\Activity;

final class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    private string $generatedPassword = '';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.users.navigation_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.users.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('admin.users.table.email'))
                    ->sortable()
                    ->searchable(),

                ...(
                    config(key: 'modules.socialite.enabled', default: false)
                        ? [TextColumn::make('provider')
                            ->label(__('admin.users.table.auth_provider'))
                            ->icon(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record->provider)->getIcon() : null)
                            ->color(fn (?User $record) => $record?->provider ? AuthenticationProvider::from($record->provider)->getColor() : null)
                            ->badge()
                            ->sortable()
                            ->searchable()]
                        : []
                ),

                TextColumn::make('role')
                    ->label(__('admin.users.table.role'))
                    ->getStateUsing(function (?User $record): string {
                        if (! $record instanceof User) {
                            return __('admin.users.table.no_role');
                        }
                        /** @var \Spatie\Permission\Models\Role|null $firstRole */
                        $firstRole = $record->roles->first();
                        if (! $firstRole) {
                            return __('admin.users.table.no_role');
                        }

                        $enum = Role::tryFrom($firstRole->name);

                        return $enum?->getLabel() ?? str($firstRole->name)->headline()->toString();
                    })
                    ->icon(function (?User $record) {
                        if (! $record instanceof User) {
                            return null;
                        }
                        /** @var \Spatie\Permission\Models\Role|null $firstRole */
                        $firstRole = $record->roles->first();
                        if (! $firstRole) {
                            return null;
                        }

                        $enum = Role::tryFrom($firstRole->name);

                        return $enum?->getIcon();
                    })
                    ->color(function (?User $record) {
                        if (! $record instanceof User) {
                            return null;
                        }
                        /** @var \Spatie\Permission\Models\Role|null $firstRole */
                        $firstRole = $record->roles->first();
                        if (! $firstRole) {
                            return null;
                        }

                        $enum = Role::tryFrom($firstRole->name);

                        return $enum?->getFilamentColor() ?? 'info';
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.users.table.status'))
                    ->getStateUsing(fn (?User $record): string => $record?->status->getLabel() ?? '')
                    ->color(function (?User $record): string {
                        if (! $record instanceof User) {
                            return 'gray';
                        }

                        return match ($record->status) {
                            ActivityStatus::ACTIVE => 'success',
                            ActivityStatus::INACTIVE => 'danger',
                            ActivityStatus::SUSPENDED => 'warning',
                            ActivityStatus::DELETED => 'danger',
                        };
                    })
                    ->badge()
                    ->sortable()
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin.users.header_actions.create'))
                    ->icon(Heroicon::OutlinedPlus)
                    ->mutateDataUsing(function (array $data): array {
                        $this->generatedPassword = Str::random(length: 10);
                        $data['password'] = $this->generatedPassword;

                        return $data;
                    })
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
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                TextInput::make('phone')
                                    ->label(__('admin.users.form.phone'))
                                    ->tel(),

                                RoleSelect::make('role')
                                    ->label(__('admin.users.form.role'))
                                    ->required(),

                                Select::make('status')
                                    ->label(__('admin.users.form.status'))
                                    ->native(false)
                                    ->options(ActivityStatus::options())
                                    ->required(),
                            ]),
                    ])->after(function (User $record): void {
                        $client = $this->getClientOwner();

                        if ($this->generatedPassword !== '' && $this->generatedPassword !== '0') {
                            $record->notify(new AccountCreated(password: $this->generatedPassword));
                        }

                        Activity::inLog('administration')
                            ->event('client.user.created')
                            ->causedBy(Auth::user())
                            ->performedOn($record)
                            ->withProperties([
                                'user' => [
                                    'id' => $record->id,
                                    'name' => $record->name,
                                    'email' => $record->email,
                                ],
                                'client' => [
                                    'id' => $client->id,
                                    'name' => $client->name,
                                ],
                            ])
                            ->log('');
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ImpersonateAction::make(),
                    ViewAction::make(),
                    EditAction::make()
                        ->after(function (User $record): void {
                            Activity::inLog('administration')
                                ->event('client.user.updated')
                                ->causedBy(Auth::user())
                                ->performedOn($record)
                                ->withProperties([
                                    'user' => [
                                        'id' => $record->id,
                                        'name' => $record->name,
                                        'email' => $record->email,
                                    ],
                                ])
                                ->log('');
                        }),
                    DeleteAction::make()
                        ->visible(fn (?User $record) => $record?->trashed())
                        ->after(function (User $record): void {
                            Activity::inLog('administration')
                                ->event('client.user.deleted')
                                ->causedBy(Auth::user())
                                ->performedOn($record)
                                ->withProperties([
                                    'user' => [
                                        'id' => $record->id,
                                        'name' => $record->name,
                                        'email' => $record->email,
                                    ],
                                    'client' => [
                                        'id' => $this->getClientOwner()->id,
                                        'name' => $this->getClientOwner()->name,
                                    ],
                                ])
                                ->log('');
                        }),
                    RestoreAction::make()
                        ->visible(fn (?User $record) => $record?->trashed())
                        ->after(function (User $record): void {
                            Activity::inLog('administration')
                                ->event('client.user.restored')
                                ->causedBy(Auth::user())
                                ->performedOn($record)
                                ->withProperties([
                                    'user' => [
                                        'id' => $record->id,
                                        'name' => $record->name,
                                        'email' => $record->email,
                                    ],
                                    'client' => [
                                        'id' => $this->getClientOwner()->id,
                                        'name' => $this->getClientOwner()->name,
                                    ],
                                ])
                                ->log('');
                        }),
                ]),
            ])
            ->recordAction(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    private function getClientOwner(): Client
    {
        $owner = $this->getOwnerRecord();
        assert($owner instanceof Client);

        return $owner;
    }
}
