<?php

namespace App\Filament\Resources;

use App\Enums\RBAC\Permission;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Session;
use Filament\Notifications\Notification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    #region UI Configuration

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    protected static ?string $model = User::class;

    /**
     * The icon of the resource.
     *
     * @var string|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * The text for the navigation label.
     *
     * @var string|null
     */
    protected static ?string $navigationLabel = 'Gebruikers';

    /**
     * The slug for the resource
     * 
     * @var string|null
     */
    protected static ?string $slug = '/users';

    /**
     * The label for this resource.
     *
     * @return string
     */
    public static function getModelLabel(): string
    {
        return 'Gebruiker';
    }

    /**
     * The plural label for this resource.
     *
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return 'Gebruikers';
    }

    #endregion

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Persoonlijke gegevens')
                    ->description('Hier bewerk je de persoonlijke informatie van deze gebruiker.')
                    ->aside()
                    ->columns(3)
                    ->schema([

                        Forms\Components\TextInput::make('first_name')
                            ->label('Voornaam')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('last_name_prefix')
                            ->label('Tussenvoegsel')
                            ->maxLength(255),
        
                        Forms\Components\TextInput::make('last_name')
                            ->label('Achternaam')
                            ->maxLength(255),
        
                        Forms\Components\TextInput::make('email')
                            ->label('E-mailadres')
                            ->columnSpan(2)
                            ->required()
                            ->email()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Sessie-informatie')
                    ->description('Hier vind je informatie over de actieve sessies van de gebruiker.')
                    ->aside()
                    ->schema([

                        Forms\Components\TextInput::make('last_login_at')
                            ->label('Laatst ingelogd op')
                            ->prefixIcon('heroicon-o-calendar')
                            ->formatStateUsing(function (?User $record): ?string {
                                return $record?->last_login_at ? $record->last_login_at->format('d-m-Y H:i') : 'Nog niet ingelogd';
                            })
                            ->disabled(),

                        Forms\Components\Repeater::make('sessions')
                            ->label('Actieve sessies')
                            ->relationship('sessions')
                            ->columns(2)
                            ->deletable(function (array $state) {

                                if(!array_key_exists('id', $state)) {
                                    return false;
                                }
                                
                                $record = $state['id'];
                                $session = Session::find($record);
                                return $session?->session_info['is_current_device'] === true ? false : true;
                            })
                            ->deleteAction(
                                fn (Action $action) => $action->action(function (array $arguments): void {
                                    $id = preg_replace('/record-/', '', $arguments['item']);
                                    $session = Session::find($id);
                                    $session?->delete();
                                })
                            )
                            ->reorderable(false)
                            ->addable(false)
                            ->collapsed()
                            ->itemLabel(function (array $state) {
                                return array_key_exists('id', $state) ? "ID: {$state['id']}" : 'ID onbekend';
                            })
                            ->schema([

                                Forms\Components\TextInput::make('ip_address')
                                    ->label('IP-adres')
                                    ->prefixIcon('heroicon-o-globe-alt')
                                    ->disabled(),

                                Forms\Components\TextInput::make('expires_at')
                                    ->label('Verloopt op')
                                    ->prefixIcon('heroicon-o-clock')
                                    ->formatStateUsing(function (Session $record): ?string {
                                        return $record?->expires_at;
                                    })
                                    ->disabled(),

                                Forms\Components\KeyValue::make('session_info')
                                    ->label('Apparaat-info')
                                    ->addable(false)
                                    ->keyLabel('Type')
                                    ->valueLabel('Details')
                                    ->deletable(false)
                                    ->editableKeys(false)
                                    ->editableValues(false)
                                    ->columnSpan(2)
                                    ->formatStateUsing(function (Session $record): array {
                                        return [
                                            'Browser' => $record?->session_info['device']['browser'],
                                            'Platform' => $record?->session_info['device']['platform'],
                                            'Apparaat' => match (true) {
                                                $record?->session_info['device']['is_desktop'] => 'Desktop',
                                                $record?->session_info['device']['is_mobile'] => 'Mobiel',
                                                $record?->session_info['device']['is_tablet'] => 'Tablet',
                                                default => 'Onbekend',
                                            },
                                        ];
                                    }),
                                    
                            ])

                    ]),

                Forms\Components\Section::make('Toegangscontrole')
                    ->description('Hier beheer je de toegang van de gebruiker tot de applicatie.')
                    ->aside()
                    ->columns(3)
                    ->schema([

                        Forms\Components\Select::make('roles')
                            ->relationship(name: 'roles', titleAttribute: 'name')
                            ->columnSpan(3)
                            ->label('Rol'),

                        Forms\Components\TextInput::make('password')
                            ->label('Nieuw wachtwoord instellen')
                            ->minLength(8)
                            ->password()
                            ->revealable()
                            ->columnSpan(2),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Column for name
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Volledige naam')
                    ->formatStateUsing(function (User $record): string {
                        return "{$record->first_name} {$record?->last_name_prefix} {$record?->last_name}";
                    })
                    ->searchable()
                    ->sortable(),

                // Column for email
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mailadres')
                    ->searchable()
                    ->sortable(),

                // Column for displaying role
                Tables\Columns\TextColumn::make('')
                    ->label('Rol')
                    ->getStateUsing(function (User $record): ?string {
                        return $record->roles->first()->name ?? 'Geen rol';
                    })
                    ->badge(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label('Inloggen als')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->color(function (User $record) {

                        /** @var \App\Models\User|null $currentUser */
                        $currentUser = Auth::user();

                        if($record->id === $currentUser?->id) {
                            return Color::Gray;
                        }
                        if(!$currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
                            return Color::Gray;
                        }

                        return Color::Green;
                    })
                    ->disabled(function (User $record): bool {

                        /** @var \App\Models\User|null $currentUser */
                        $currentUser = Auth::user();

                        if($record->id === $currentUser?->id) {
                            return true;
                        }
                        if(!$currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
                            return true;
                        }

                        return false;
                    })
                    ->action(function (User $record) {

                        /** @var \App\Models\User|null $currentUser */
                        $currentUser = Auth::user();

                        if(!$currentUser || !$currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
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
                
                Tables\Actions\EditAction::make()
                    ->label('Bewerken'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
