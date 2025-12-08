<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\Schemas;

use App\Enums\RBAC\Permission as RBACPermission;
use App\Enums\RBAC\Role as RBACRole;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Guava\IconPicker\Forms\Components\IconPicker;
use Spatie\Permission\Models\Role;

final class RoleForm
{
    public const PERMISSION_FIELDS = [
        'general_permissions',
        'user_permissions',
        'role_permissions',
        'api_token_permissions',
        'client_permissions',
        'socialite_permissions',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.rbac.roles.form.basic_information'))
                    ->columnSpanFull()
                    ->columns(fn (?Role $record): int => $record && self::isSystemRole($record) ? 2 : 3)
                    ->schema([
                        TextInput::make('label')
                            ->label(__('admin.rbac.roles.form.label'))
                            ->formatStateUsing(function (?Role $record): string {
                                if (! $record instanceof Role) {
                                    return '';
                                }

                                $enumCase = collect(RBACRole::cases())
                                    ->first(fn (RBACRole $enum): bool => $enum->value === $record->name);

                                return $enumCase?->getLabel() ?? '';
                            })
                            ->disabled()
                            ->visible(fn (?Role $record): bool => $record && self::isSystemRole($record)),

                        TextInput::make('name')
                            ->label(__('admin.rbac.roles.form.name'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?Role $record): bool => $record && self::isSystemRole($record))
                            ->formatStateUsing(function ($state, ?Role $record) {
                                if ($record instanceof Role) {
                                    return $record->name ?? '-';
                                }

                                if (is_string($state) && $state !== '') {
                                    // Lowercase, replace spaces with underscores
                                    return str_replace(' ', '_', mb_strtolower($state));
                                }

                                return '-';
                            })
                            ->validationMessages([
                                'unique' => __('admin.rbac.roles.form.name_unique'),
                            ]),

                        IconPicker::make('icon')
                            ->label(__('admin.rbac.roles.form.icon'))
                            ->required()
                            ->default('heroicon-o-user')
                            ->visible(fn (?Role $record): bool => ! $record || ! self::isSystemRole($record)),

                        Select::make('color')
                            ->label(__('admin.rbac.roles.form.color'))
                            ->required()
                            ->default('gray')
                            ->options([
                                'success' => 'Green',
                                'warning' => 'Yellow',
                                'danger' => 'Red',
                                'info' => 'Blue',
                                'gray' => 'Gray',
                            ])
                            ->visible(fn (?Role $record): bool => ! $record || ! self::isSystemRole($record)),
                    ]),

                Section::make(__('admin.rbac.roles.form.permissions'))
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(2)
                    ->hiddenOn('view')
                    ->schema([
                        // General/System Permissions
                        Section::make(__('admin.rbac.roles.form.sections.general'))
                            ->schema([
                                self::makePermissionCheckboxList('general_permissions', self::getGeneralPermissionsList()),
                            ]),

                        // User Management Permissions
                        Section::make(__('admin.rbac.roles.form.sections.users'))
                            ->schema([
                                self::makePermissionCheckboxList('user_permissions', self::getUserPermissionsList()),
                            ]),

                        // Role Management Permissions
                        Section::make(__('admin.rbac.roles.form.sections.roles'))
                            ->schema([
                                self::makePermissionCheckboxList('role_permissions', self::getRolePermissionsList()),
                            ]),

                        // API Tokens Permissions
                        Section::make(__('admin.rbac.roles.form.sections.api_tokens'))
                            ->visible(config('modules.api.enabled', false))
                            ->schema([
                                self::makePermissionCheckboxList('api_token_permissions', self::getApiTokenPermissionsList()),
                            ]),

                        // Client Permissions (conditional)
                        Section::make(__('admin.rbac.roles.form.sections.clients'))
                            ->visible(config('modules.clients.enabled', false))
                            ->schema([
                                self::makePermissionCheckboxList('client_permissions', self::getClientPermissionsList()),
                            ]),

                        // Socialite Permissions (conditional)
                        Section::make(__('admin.rbac.roles.form.sections.socialite'))
                            ->visible(config('modules.socialite.enabled', false))
                            ->schema([
                                self::makePermissionCheckboxList('socialite_permissions', self::getSocialitePermissionsList()),
                            ]),
                    ]),
            ]);
    }

    private static function isSystemRole(Role $record): bool
    {
        return collect(RBACRole::cases())
            ->contains(fn (RBACRole $enum): bool => $enum->value === $record->name);
    }

    /**
     * @param  array<int, RBACPermission>  $permissions
     */
    private static function makePermissionCheckboxList(string $name, array $permissions): CheckboxList
    {
        $options = self::filterAndMapPermissions($permissions);
        $columns = count($options) > 4 ? 2 : 1;
        $permissionNames = array_keys($options);

        // Get super admin only permissions
        $superAdminOnlyPermissions = collect($permissions)
            ->filter(fn (RBACPermission $permission): bool => $permission->isSuperAdminOnly())
            ->map(fn (RBACPermission $permission): string => $permission->value)
            ->toArray();

        return CheckboxList::make($name)
            ->hiddenLabel()
            ->options($options)
            ->columns($columns)
            ->afterStateHydrated(function (CheckboxList $component, ?Role $record) use ($permissionNames): void {
                if (! $record instanceof Role) {
                    return;
                }

                $existingPermissions = $record->permissions
                    ->pluck('name')
                    ->intersect($permissionNames)
                    ->values()
                    ->toArray();

                $component->state($existingPermissions);
            })
            ->disabled(fn (?Role $record): bool => $record && self::isSystemRole($record))
            ->dehydrated(fn (?Role $record): bool => ! ($record && self::isSystemRole($record)))
            ->disableOptionWhen(fn (string $value): bool => in_array($value, $superAdminOnlyPermissions))
            ->descriptions(
                collect($permissions)
                    ->filter(fn (RBACPermission $permission): bool => $permission->isSuperAdminOnly())
                    ->mapWithKeys(fn (RBACPermission $permission): array => [
                        $permission->value => __('admin.rbac.roles.form.super_admin_only_permission'),
                    ])
                    ->toArray()
            );
    }

    /**
     * @return array<int, RBACPermission>
     */
    private static function getGeneralPermissionsList(): array
    {
        return [
            RBACPermission::ACCESS_CONTROL_PANEL,
            RBACPermission::IMPERSONATE_USERS,
            RBACPermission::ACCESS_ACTIVITY_LOGS,
            RBACPermission::MANAGE_SETTINGS,
            RBACPermission::HAS_API_ACCESS,
            RBACPermission::ACCESS_BACKUPS,
            RBACPermission::ACCESS_HEALTH_CHECK,
        ];
    }

    /**
     * @return array<int, RBACPermission>
     */
    private static function getUserPermissionsList(): array
    {
        return [
            RBACPermission::CREATE_USERS,
            RBACPermission::READ_USERS,
            RBACPermission::UPDATE_USERS,
            RBACPermission::DELETE_USERS,
            RBACPermission::RESTORE_USERS,
        ];
    }

    /**
     * @return array<int, RBACPermission>
     */
    private static function getRolePermissionsList(): array
    {
        return [
            RBACPermission::CREATE_ROLES,
            RBACPermission::READ_ROLES,
            RBACPermission::UPDATE_ROLES,
            RBACPermission::DELETE_ROLES,
            RBACPermission::RESTORE_ROLES,
        ];
    }

    /**
     * @return array<int, RBACPermission>
     */
    private static function getApiTokenPermissionsList(): array
    {
        return [
            RBACPermission::CREATE_API_TOKENS,
            RBACPermission::READ_API_TOKENS,
            RBACPermission::UPDATE_API_TOKENS,
            RBACPermission::DELETE_API_TOKENS,
        ];
    }

    /**
     * @return array<int, RBACPermission>
     */
    private static function getClientPermissionsList(): array
    {
        return [
            RBACPermission::CREATE_CLIENTS,
            RBACPermission::READ_CLIENTS,
            RBACPermission::UPDATE_CLIENTS,
            RBACPermission::DELETE_CLIENTS,
            RBACPermission::RESTORE_CLIENTS,
        ];
    }

    /**
     * @return array<int, RBACPermission>
     */
    private static function getSocialitePermissionsList(): array
    {
        return [
            RBACPermission::UPDATE_SOCIALITE_PROVIDERS,
        ];
    }

    /**
     * @param  array<int, RBACPermission>  $permissions
     * @return array<string, string>
     */
    private static function filterAndMapPermissions(array $permissions): array
    {
        return collect($permissions)
            ->filter(fn (RBACPermission $permission): bool => $permission->shouldSync())
            ->mapWithKeys(fn (RBACPermission $permission): array => [
                $permission->value => $permission->getLabel(),
            ])
            ->toArray();
    }
}
