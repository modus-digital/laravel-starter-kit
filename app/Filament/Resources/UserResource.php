<?php

namespace App\Filament\Resources;

use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Session;
use App\Models\User;
use DateTime;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Override;

class UserResource extends Resource
{
    #region UI Configuration

    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = User::class;

    /**
     * The icon of the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * The text for the navigation label.
     */
    protected static ?string $navigationLabel = 'Gebruikers';

    /**
     * The slug for the resource
     */
    protected static ?string $slug = '/users';

    /**
     * The label for this resource.
     */
    #[Override]
    public static function getModelLabel(): string
    {
        return 'Gebruiker';
    }

    /**
     * The plural label for this resource.
     */
    #[Override]
    public static function getPluralModelLabel(): string
    {
        return 'Gebruikers';
    }

    #endregion

    #[Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Persoonlijke gegevens')
                    ->description('Hier bewerk je de persoonlijke informatie van deze gebruiker.')
                    ->aside()
                    ->columns(3)
                    ->schema([

                        TextInput::make('first_name')
                            ->label('Voornaam')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name_prefix')
                            ->label('Tussenvoegsel')
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label('Achternaam')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-mailadres')
                            ->columnSpan(2)
                            ->required()
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make('Sessie-informatie')
                    ->description('Hier vind je informatie over de actieve sessies van de gebruiker.')
                    ->aside()
                    ->hidden(fn (string $operation): bool => $operation !== 'edit')
                    ->schema([

                        TextInput::make('last_login_at')
                            ->label('Laatst ingelogd op')
                            ->prefixIcon('heroicon-o-calendar')
                            ->formatStateUsing(
                                fn (User $record): string => $record->last_login_at ?
                                    ($record->last_login_at instanceof DateTime ?
                                        $record->last_login_at->format('d-m-Y H:i') :
                                        $record->last_login_at) :
                                    'Nog niet ingelogd'
                            )
                            ->disabled(),

                        Repeater::make('sessions')
                            ->label('Actieve sessies')
                            ->relationship('sessions')
                            ->columns(2)
                            ->deletable(function (array $state): bool {

                                if (! array_key_exists('id', $state)) {
                                    return false;
                                }

                                $record = $state['id'];
                                $session = Session::find($record);

                                return $session?->session_info['is_current_device'] !== true;
                            })
                            ->deleteAction(
                                fn (Action $action): Action => $action->action(function (array $arguments): void {
                                    $id = preg_replace('/record-/', '', (string) $arguments['item']);
                                    $session = Session::find($id);
                                    $session?->delete();
                                })
                            )
                            ->reorderable(false)
                            ->addable(false)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => array_key_exists('id', $state) ? 'ID: ' . $state['id'] : 'ID onbekend')
                            ->schema([

                                TextInput::make('ip_address')
                                    ->label('IP-adres')
                                    ->prefixIcon('heroicon-o-globe-alt')
                                    ->disabled(),

                                TextInput::make('expires_at')
                                    ->label('Verloopt op')
                                    ->prefixIcon('heroicon-o-clock')
                                    ->formatStateUsing(fn (?Session $record): ?string => $record?->expires_at)
                                    ->disabled(),

                                KeyValue::make('session_info')
                                    ->label('Apparaat-info')
                                    ->addable(false)
                                    ->keyLabel('Type')
                                    ->valueLabel('Details')
                                    ->deletable(false)
                                    ->editableKeys(false)
                                    ->editableValues(false)
                                    ->columnSpan(2)
                                    ->formatStateUsing(fn (?Session $record): array => [
                                        'Browser' => $record?->session_info['device']['browser'],
                                        'Platform' => $record?->session_info['device']['platform'],
                                        'Apparaat' => match (true) {
                                            $record?->session_info['device']['is_desktop'] => 'Desktop',
                                            $record?->session_info['device']['is_mobile'] => 'Mobiel',
                                            $record?->session_info['device']['is_tablet'] => 'Tablet',
                                            default => 'Onbekend',
                                        },
                                    ]),

                            ]),

                    ]),

                Section::make('Toegangscontrole')
                    ->description('Hier beheer je de toegang van de gebruiker tot de applicatie.')
                    ->aside()
                    ->columns(3)
                    ->schema([

                        Select::make('roles')
                            ->relationship(name: 'roles', titleAttribute: 'name')
                            ->columnSpan(3)
                            ->label('Rol'),

                        TextInput::make('password')
                            ->label('Nieuw wachtwoord instellen')
                            ->minLength(8)
                            ->password()
                            ->revealable()
                            ->columnSpan(2),

                    ]),

            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Column for name
                TextColumn::make('first_name')
                    ->label('Volledige naam')
                    ->formatStateUsing(fn (User $record): string => sprintf('%s %s %s', $record->first_name, $record->last_name_prefix, $record->last_name))
                    ->searchable()
                    ->sortable(),

                // Column for email
                TextColumn::make('email')
                    ->label('E-mailadres')
                    ->searchable()
                    ->sortable(),

                // Column for displaying role
                TextColumn::make('')
                    ->label('Rol')
                    ->getStateUsing(fn (User $record): string => Role::from($record->roles->first()?->name)->displayName() ?? 'Geen rol')
                    ->badge(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label('Inloggen als')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->color(function (User $record): array {

                        /** @var User $currentUser */
                        $currentUser = Auth::user();

                        if ($record->id === $currentUser->id) {
                            return Color::Gray;
                        }

                        if (! $currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
                            return Color::Gray;
                        }

                        return Color::Green;
                    })
                    ->disabled(function (User $record): bool {

                        /** @var User $currentUser */
                        $currentUser = Auth::user();

                        if ($record->id === $currentUser->id) {
                            return true;
                        }

                        return ! $currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS);
                    })
                    ->action(function (User $record) {

                        /** @var User $currentUser */
                        $currentUser = Auth::user();

                        if (! $currentUser || ! $currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
                            Notification::make()
                                ->title('Je hebt geen toegang tot deze actie')
                                ->body('Je hebt niet de juiste rechten om deze actie uit te voeren.')
                                ->color(Color::Red)
                                ->send();

                            return;
                        }

                        session()->put('impersonating_user_id', $currentUser->id);
                        session()->put('impersonating_return_url', url()->previous());
                        session()->put('can_bypass_2fa', true);

                        Auth::login($record);

                        return redirect()->to('/dashboard');
                    }),

                EditAction::make()
                    ->label('Bewerken'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
