<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RBAC\{Permission, Role};
use App\Filament\Resources\UserResource\Pages\{CreateUser, EditUser, ListUsers};
use App\Models\{Session, User};
use DateTime;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\{KeyValue, Repeater, Section, Select, TextInput};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Resource for managing users in the application.
 *
 * This resource provides CRUD operations for users, including:
 * - Creating and editing user profiles
 * - Managing user roles and permissions
 * - Viewing session information
 * - User impersonation functionality
 *
 * @since 1.0.0
 */
class UserResource extends Resource
{
    /**
     * The Eloquent model that this resource corresponds to.
     */
    protected static ?string $model = User::class;

    /**
     * The navigation icon displayed in the admin panel.
     */
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * The URL slug for this resource.
     */
    protected static ?string $slug = '/users';

    /**
     * Get the navigation label displayed in the admin panel.
     *
     * @return string The navigation label
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.resources.users.label.plural');
    }

    /**
     * Get the navigation group this resource belongs to.
     *
     * @return string|null The navigation group name
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.beheer');
    }

    /**
     * Get the singular label for this resource.
     *
     * @return string The singular model label
     */
    public static function getModelLabel(): string
    {
        return __('admin.resources.users.label.singular');
    }

    /**
     * Get the plural label for this resource.
     *
     * @return string The plural model label
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.users.label.plural');
    }

    /**
     * Define the form schema for user management.
     *
     * @param Form $form The form instance
     * @return Form The configured form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.resources.users.form.sections.personal_information.label'))
                    ->description(__('admin.resources.users.form.sections.personal_information.description'))
                    ->aside()
                    ->columns(3)
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('admin.resources.users.form.sections.personal_information.first_name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name_prefix')
                            ->label(__('admin.resources.users.form.sections.personal_information.last_name_prefix'))
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label(__('admin.resources.users.form.sections.personal_information.last_name'))
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('admin.resources.users.form.sections.personal_information.email'))
                            ->columnSpan(2)
                            ->required()
                            ->email()
                            ->maxLength(255),
                    ]),

                Section::make(__('admin.resources.users.form.sections.session_information.label'))
                    ->description(__('admin.resources.users.form.sections.session_information.description'))
                    ->aside()
                    ->hidden(fn(string $operation): bool => $operation !== 'edit')
                    ->schema([
                        TextInput::make('last_login_at')
                            ->label(__('admin.resources.users.form.sections.session_information.last_login_at'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->formatStateUsing(
                                fn(?User $record): string => $record?->last_login_at ?
                                    ($record?->last_login_at instanceof DateTime ?
                                        $record?->last_login_at->format('d-m-Y H:i') :
                                        $record?->last_login_at) :
                                    'Not logged in yet'
                            )
                            ->disabled(),

                        Repeater::make('sessions')
                            ->label(__('admin.resources.users.form.sections.session_information.sessions.label'))
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
                                fn(Action $action): Action => $action->action(function (array $arguments): void {
                                    $id = preg_replace('/record-/', '', (string) $arguments['item']);
                                    $session = Session::find($id);
                                    $session?->delete();
                                })
                            )
                            ->reorderable(false)
                            ->addable(false)
                            ->collapsed()
                            ->itemLabel(fn(array $state): string => array_key_exists('id', $state) ? 'ID: ' . $state['id'] : 'ID unknown')
                            ->schema([
                                TextInput::make('ip_address')
                                    ->label(__('admin.resources.users.form.sections.session_information.sessions.ip_address'))
                                    ->prefixIcon('heroicon-o-globe-alt')
                                    ->disabled(),

                                TextInput::make('expires_at')
                                    ->label(__('admin.resources.users.form.sections.session_information.sessions.expires_at'))
                                    ->prefixIcon('heroicon-o-clock')
                                    ->formatStateUsing(fn(?Session $record): ?string => $record?->expires_at)
                                    ->disabled(),

                                KeyValue::make('session_info')
                                    ->label(__('admin.resources.users.form.sections.session_information.sessions.device_info'))
                                    ->addable(false)
                                    ->keyLabel('Type')
                                    ->valueLabel('Details')
                                    ->deletable(false)
                                    ->editableKeys(false)
                                    ->editableValues(false)
                                    ->columnSpan(2)
                                    ->formatStateUsing(fn(?Session $record): array => [
                                        __('admin.resources.users.form.sections.session_information.sessions.browser') => $record?->session_info['device']['browser'],
                                        __('admin.resources.users.form.sections.session_information.sessions.platform') => $record?->session_info['device']['platform'],
                                        __('admin.resources.users.form.sections.session_information.sessions.device') => match (true) {
                                            $record?->session_info['device']['is_desktop'] => __('admin.resources.users.form.sections.session_information.sessions.desktop'),
                                            $record?->session_info['device']['is_mobile'] => __('admin.resources.users.form.sections.session_information.sessions.mobile'),
                                            $record?->session_info['device']['is_tablet'] => __('admin.resources.users.form.sections.session_information.sessions.tablet'),
                                            default => __('admin.resources.users.form.sections.session_information.sessions.unknown'),
                                        },
                                    ]),
                            ]),
                    ]),

                Section::make(__('admin.resources.users.form.sections.access_control.label'))
                    ->description(__('admin.resources.users.form.sections.access_control.description'))
                    ->aside()
                    ->columns(3)
                    ->schema([
                        Select::make('role')
                            ->relationship(name: 'roles', titleAttribute: 'name')
                            ->multiple()
                            ->maxItems(1)
                            ->preload()
                            ->columnSpan(3)
                            ->required()
                            ->label(__('admin.resources.users.form.sections.access_control.role')),

                        TextInput::make('password')
                            ->label(__('admin.resources.users.form.sections.access_control.new_password'))
                            ->minLength(8)
                            ->password()
                            ->required(fn(string $operation) => $operation === 'create')
                            ->revealable()
                            ->columnSpan(2),
                    ]),
            ]);
    }

    /**
     * Define the table schema for displaying users.
     *
     * @param Table $table The table instance
     * @return Table The configured table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('admin.resources.users.table.name'))
                    ->formatStateUsing(fn(?User $record): string => sprintf('%s %s %s', $record?->first_name, $record?->last_name_prefix, $record?->last_name))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('admin.resources.users.table.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('')
                    ->label(__('admin.resources.users.table.role'))
                    ->getStateUsing(fn(?User $record): string => Role::from($record?->roles->first()?->name)->displayName() ?? 'No role')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label(__('admin.resources.users.actions.impersonate'))
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->color(function (?User $record): array {
                        /** @var User $currentUser */
                        $currentUser = Auth::user();

                        if ($record?->id === $currentUser->id) {
                            return Color::Gray;
                        }

                        if (! $currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
                            return Color::Gray;
                        }

                        return Color::Green;
                    })
                    ->disabled(function (?User $record): bool {
                        /** @var User $currentUser */
                        $currentUser = Auth::user();

                        if ($record?->id === $currentUser->id) {
                            return true;
                        }

                        return ! $currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS);
                    })
                    ->action(function (?User $record) {
                        /** @var User $currentUser */
                        $currentUser = Auth::user();

                        if (! $currentUser || ! $currentUser->hasPermissionTo(Permission::CAN_IMPERSONATE_USERS)) {
                            Notification::make()
                                ->title(__('admin.resources.users.notifications.impersonate.title'))
                                ->body(__('admin.resources.users.notifications.impersonate.message'))
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
                    ->label(__('admin.resources.users.actions.edit')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Get the relation managers for this resource.
     *
     * @return array<string> Array of relation manager classes
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Get the pages associated with this resource.
     *
     * @return array<string, mixed> Array of page routes
     */
    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
