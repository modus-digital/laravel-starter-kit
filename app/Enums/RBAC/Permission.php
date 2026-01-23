<?php

declare(strict_types=1);

namespace App\Enums\RBAC;

enum Permission: string
{
    // General/System permissions
    case ACCESS_CONTROL_PANEL = 'access:control-panel';
    case IMPERSONATE_USERS = 'access:impersonate-users';
    case ACCESS_BACKUPS = 'access:backups';
    case ACCESS_HEALTH_CHECK = 'access:health-check';
    case ACCESS_ACTIVITY_LOGS = 'access:activity-logs';
    case MANAGE_SETTINGS = 'manage:settings';
    case HAS_API_ACCESS = 'access:api';

    // Users CRUD
    case CREATE_USERS = 'create:users';
    case READ_USERS = 'read:users';
    case UPDATE_USERS = 'update:users';
    case DELETE_USERS = 'delete:users';
    case RESTORE_USERS = 'restore:users';

    // Roles CRUD
    case CREATE_ROLES = 'create:roles';
    case READ_ROLES = 'read:roles';
    case UPDATE_ROLES = 'update:roles';
    case DELETE_ROLES = 'delete:roles';
    case RESTORE_ROLES = 'restore:roles';

    // API Tokens CRUD (no restore)
    case CREATE_API_TOKENS = 'create:api-tokens';
    case READ_API_TOKENS = 'read:api-tokens';
    case UPDATE_API_TOKENS = 'update:api-tokens';
    case DELETE_API_TOKENS = 'delete:api-tokens';

    // Clients CRUD (conditional - if module enabled)
    case CREATE_CLIENTS = 'create:clients';
    case READ_CLIENTS = 'read:clients';
    case UPDATE_CLIENTS = 'update:clients';
    case DELETE_CLIENTS = 'delete:clients';
    case RESTORE_CLIENTS = 'restore:clients';

    public function getLabel(): string
    {
        return match ($this) {
            // Simple permissions
            self::ACCESS_CONTROL_PANEL => __('enums.rbac.permission.label.access_control_panel'),
            self::IMPERSONATE_USERS => __('enums.rbac.permission.label.impersonate_users'),
            self::ACCESS_BACKUPS => __('enums.rbac.permission.label.access_backups'),
            self::ACCESS_HEALTH_CHECK => __('enums.rbac.permission.label.access_health_check'),
            self::ACCESS_ACTIVITY_LOGS => __('enums.rbac.permission.label.access_activity_logs'),
            self::MANAGE_SETTINGS => __('enums.rbac.permission.label.manage_settings'),
            self::HAS_API_ACCESS => __('enums.rbac.permission.label.has_api_access'),

            // Users
            self::CREATE_USERS => __('enums.rbac.permission.label.create_users'),
            self::READ_USERS => __('enums.rbac.permission.label.read_users'),
            self::UPDATE_USERS => __('enums.rbac.permission.label.update_users'),
            self::DELETE_USERS => __('enums.rbac.permission.label.delete_users'),
            self::RESTORE_USERS => __('enums.rbac.permission.label.restore_users'),

            // Roles
            self::CREATE_ROLES => __('enums.rbac.permission.label.create_roles'),
            self::READ_ROLES => __('enums.rbac.permission.label.read_roles'),
            self::UPDATE_ROLES => __('enums.rbac.permission.label.update_roles'),
            self::DELETE_ROLES => __('enums.rbac.permission.label.delete_roles'),
            self::RESTORE_ROLES => __('enums.rbac.permission.label.restore_roles'),

            // API Tokens
            self::CREATE_API_TOKENS => __('enums.rbac.permission.label.create_api_tokens'),
            self::READ_API_TOKENS => __('enums.rbac.permission.label.read_api_tokens'),
            self::UPDATE_API_TOKENS => __('enums.rbac.permission.label.update_api_tokens'),
            self::DELETE_API_TOKENS => __('enums.rbac.permission.label.delete_api_tokens'),

            // Clients
            self::CREATE_CLIENTS => __('enums.rbac.permission.label.create_clients'),
            self::READ_CLIENTS => __('enums.rbac.permission.label.read_clients'),
            self::UPDATE_CLIENTS => __('enums.rbac.permission.label.update_clients'),
            self::DELETE_CLIENTS => __('enums.rbac.permission.label.delete_clients'),
            self::RESTORE_CLIENTS => __('enums.rbac.permission.label.restore_clients'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            // Simple permissions
            self::ACCESS_CONTROL_PANEL => __('enums.rbac.permission.description.access_control_panel'),
            self::IMPERSONATE_USERS => __('enums.rbac.permission.description.impersonate_users'),
            self::ACCESS_BACKUPS => __('enums.rbac.permission.description.access_backups'),
            self::ACCESS_HEALTH_CHECK => __('enums.rbac.permission.description.access_health_check'),
            self::ACCESS_ACTIVITY_LOGS => __('enums.rbac.permission.description.access_activity_logs'),
            self::MANAGE_SETTINGS => __('enums.rbac.permission.description.manage_settings'),
            self::HAS_API_ACCESS => __('enums.rbac.permission.description.has_api_access'),

            // Users
            self::CREATE_USERS => __('enums.rbac.permission.description.create_users'),
            self::READ_USERS => __('enums.rbac.permission.description.read_users'),
            self::UPDATE_USERS => __('enums.rbac.permission.description.update_users'),
            self::DELETE_USERS => __('enums.rbac.permission.description.delete_users'),
            self::RESTORE_USERS => __('enums.rbac.permission.description.restore_users'),

            // Roles
            self::CREATE_ROLES => __('enums.rbac.permission.description.create_roles'),
            self::READ_ROLES => __('enums.rbac.permission.description.read_roles'),
            self::UPDATE_ROLES => __('enums.rbac.permission.description.update_roles'),
            self::DELETE_ROLES => __('enums.rbac.permission.description.delete_roles'),
            self::RESTORE_ROLES => __('enums.rbac.permission.description.restore_roles'),

            // API Tokens
            self::CREATE_API_TOKENS => __('enums.rbac.permission.description.create_api_tokens'),
            self::READ_API_TOKENS => __('enums.rbac.permission.description.read_api_tokens'),
            self::UPDATE_API_TOKENS => __('enums.rbac.permission.description.update_api_tokens'),
            self::DELETE_API_TOKENS => __('enums.rbac.permission.description.delete_api_tokens'),

            // Clients
            self::CREATE_CLIENTS => __('enums.rbac.permission.description.create_clients'),
            self::READ_CLIENTS => __('enums.rbac.permission.description.read_clients'),
            self::UPDATE_CLIENTS => __('enums.rbac.permission.description.update_clients'),
            self::DELETE_CLIENTS => __('enums.rbac.permission.description.delete_clients'),
            self::RESTORE_CLIENTS => __('enums.rbac.permission.description.restore_clients'),
        };
    }

    /**
     * Check if this permission should be synced based on module configuration
     */
    public function shouldSync(): bool
    {
        return match ($this) {
            // Clients permissions - only if clients module is enabled
            self::CREATE_CLIENTS,
            self::READ_CLIENTS,
            self::UPDATE_CLIENTS,
            self::DELETE_CLIENTS,
            self::RESTORE_CLIENTS => config('modules.clients.enabled', false),

            // API access - only if API module is enabled
            self::HAS_API_ACCESS,
            self::CREATE_API_TOKENS,
            self::READ_API_TOKENS,
            self::UPDATE_API_TOKENS,
            self::DELETE_API_TOKENS => config('modules.api.enabled', false),

            // All other permissions should always sync
            default => true,
        };
    }

    /**
     * Check if this is a super admin only permission that cannot be assigned to custom roles
     */
    public function isSuperAdminOnly(): bool
    {
        return match ($this) {
            self::ACCESS_BACKUPS,
            self::ACCESS_HEALTH_CHECK => true,
            default => false,
        };
    }
}
