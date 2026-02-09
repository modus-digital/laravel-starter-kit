<?php

declare(strict_types=1);

namespace App\Enums\RBAC;

enum Permission: string
{
    // Internal-only permissions (cannot be assigned to external roles)
    case AccessControlPanel = 'access:control-panel';
    case ImpersonateUsers = 'access:impersonate-users';
    case ManageRoles = 'manage:roles';

    // User permissions
    case CreateUsers = 'create:users';
    case ViewUsers = 'view:users';
    case UpdateUsers = 'update:users';
    case DeleteUsers = 'delete:users';
    case ViewAnyUsers = 'view-any:users';
    case RestoreUsers = 'restore:users';
    case ForceDeleteUsers = 'force-delete:users';

    // Role permissions
    case CreateRoles = 'create:roles';
    case ViewRoles = 'view:roles';
    case UpdateRoles = 'update:roles';
    case DeleteRoles = 'delete:roles';
    case ViewAnyRoles = 'view-any:roles';
    case RestoreRoles = 'restore:roles';
    case ForceDeleteRoles = 'force-delete:roles';

    // Client permissions
    case CreateClients = 'create:clients';
    case ViewClients = 'view:clients';
    case UpdateClients = 'update:clients';
    case DeleteClients = 'delete:clients';
    case ViewAnyClients = 'view-any:clients';
    case RestoreClients = 'restore:clients';
    case ForceDeleteClients = 'force-delete:clients';

    // Task permissions
    case CreateTasks = 'create:tasks';
    case ViewTasks = 'view:tasks';
    case UpdateTasks = 'update:tasks';
    case DeleteTasks = 'delete:tasks';
    case ViewAnyTasks = 'view-any:tasks';
    case RestoreTasks = 'restore:tasks';
    case ForceDeleteTasks = 'force-delete:tasks';

    /**
     * Get all permissions for a specific entity
     *
     * @return array<self>
     */
    public static function forEntity(string $entity): array
    {
        return array_filter(
            self::cases(),
            fn (self $permission): bool => $permission->getCategory() === $entity
        );
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::AccessControlPanel => __('enums.rbac.permission.label.access_control_panel'),
            self::ImpersonateUsers => __('enums.rbac.permission.label.impersonate_users'),
            self::ManageRoles => __('enums.rbac.permission.label.manage_roles'),

            self::CreateUsers => __('enums.rbac.permission.label.create_users'),
            self::ViewUsers => __('enums.rbac.permission.label.view_users'),
            self::UpdateUsers => __('enums.rbac.permission.label.update_users'),
            self::DeleteUsers => __('enums.rbac.permission.label.delete_users'),
            self::ViewAnyUsers => __('enums.rbac.permission.label.view_any_users'),
            self::RestoreUsers => __('enums.rbac.permission.label.restore_users'),
            self::ForceDeleteUsers => __('enums.rbac.permission.label.force_delete_users'),

            self::CreateRoles => __('enums.rbac.permission.label.create_roles'),
            self::ViewRoles => __('enums.rbac.permission.label.view_roles'),
            self::UpdateRoles => __('enums.rbac.permission.label.update_roles'),
            self::DeleteRoles => __('enums.rbac.permission.label.delete_roles'),
            self::ViewAnyRoles => __('enums.rbac.permission.label.view_any_roles'),
            self::RestoreRoles => __('enums.rbac.permission.label.restore_roles'),
            self::ForceDeleteRoles => __('enums.rbac.permission.label.force_delete_roles'),

            self::CreateClients => __('enums.rbac.permission.label.create_clients'),
            self::ViewClients => __('enums.rbac.permission.label.view_clients'),
            self::UpdateClients => __('enums.rbac.permission.label.update_clients'),
            self::DeleteClients => __('enums.rbac.permission.label.delete_clients'),
            self::ViewAnyClients => __('enums.rbac.permission.label.view_any_clients'),
            self::RestoreClients => __('enums.rbac.permission.label.restore_clients'),
            self::ForceDeleteClients => __('enums.rbac.permission.label.force_delete_clients'),

            self::CreateTasks => __('enums.rbac.permission.label.create_tasks'),
            self::ViewTasks => __('enums.rbac.permission.label.view_tasks'),
            self::UpdateTasks => __('enums.rbac.permission.label.update_tasks'),
            self::DeleteTasks => __('enums.rbac.permission.label.delete_tasks'),
            self::ViewAnyTasks => __('enums.rbac.permission.label.view_any_tasks'),
            self::RestoreTasks => __('enums.rbac.permission.label.restore_tasks'),
            self::ForceDeleteTasks => __('enums.rbac.permission.label.force_delete_tasks'),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::AccessControlPanel => __('enums.rbac.permission.description.access_control_panel'),
            self::ImpersonateUsers => __('enums.rbac.permission.description.impersonate_users'),
            self::ManageRoles => __('enums.rbac.permission.description.manage_roles'),

            self::CreateUsers => __('enums.rbac.permission.description.create_users'),
            self::ViewUsers => __('enums.rbac.permission.description.view_users'),
            self::UpdateUsers => __('enums.rbac.permission.description.update_users'),
            self::DeleteUsers => __('enums.rbac.permission.description.delete_users'),
            self::ViewAnyUsers => __('enums.rbac.permission.description.view_any_users'),
            self::RestoreUsers => __('enums.rbac.permission.description.restore_users'),
            self::ForceDeleteUsers => __('enums.rbac.permission.description.force_delete_users'),

            self::CreateRoles => __('enums.rbac.permission.description.create_roles'),
            self::ViewRoles => __('enums.rbac.permission.description.view_roles'),
            self::UpdateRoles => __('enums.rbac.permission.description.update_roles'),
            self::DeleteRoles => __('enums.rbac.permission.description.delete_roles'),
            self::ViewAnyRoles => __('enums.rbac.permission.description.view_any_roles'),
            self::RestoreRoles => __('enums.rbac.permission.description.restore_roles'),
            self::ForceDeleteRoles => __('enums.rbac.permission.description.force_delete_roles'),

            self::CreateClients => __('enums.rbac.permission.description.create_clients'),
            self::ViewClients => __('enums.rbac.permission.description.view_clients'),
            self::UpdateClients => __('enums.rbac.permission.description.update_clients'),
            self::DeleteClients => __('enums.rbac.permission.description.delete_clients'),
            self::ViewAnyClients => __('enums.rbac.permission.description.view_any_clients'),
            self::RestoreClients => __('enums.rbac.permission.description.restore_clients'),
            self::ForceDeleteClients => __('enums.rbac.permission.description.force_delete_clients'),

            self::CreateTasks => __('enums.rbac.permission.description.create_tasks'),
            self::ViewTasks => __('enums.rbac.permission.description.view_tasks'),
            self::UpdateTasks => __('enums.rbac.permission.description.update_tasks'),
            self::DeleteTasks => __('enums.rbac.permission.description.delete_tasks'),
            self::ViewAnyTasks => __('enums.rbac.permission.description.view_any_tasks'),
            self::RestoreTasks => __('enums.rbac.permission.description.restore_tasks'),
            self::ForceDeleteTasks => __('enums.rbac.permission.description.force_delete_tasks'),
        };
    }

    /**
     * Check if this permission should be synced based on module configuration
     */
    public function shouldSync(): bool
    {
        // All permissions should always sync
        return true;
    }

    /**
     * Check if this is an internal-only permission that cannot be assigned to external roles
     */
    public function isInternalOnly(): bool
    {
        return match ($this) {
            self::AccessControlPanel,
            self::ImpersonateUsers,
            self::ManageRoles => true,
            default => false,
        };
    }

    /**
     * Get the category/entity this permission belongs to for UI grouping
     */
    public function getCategory(): string
    {
        return match ($this) {
            self::AccessControlPanel,
            self::ImpersonateUsers,
            self::ManageRoles => 'access',

            self::CreateUsers,
            self::ViewUsers,
            self::UpdateUsers,
            self::DeleteUsers,
            self::ViewAnyUsers,
            self::RestoreUsers,
            self::ForceDeleteUsers => 'users',

            self::CreateRoles,
            self::ViewRoles,
            self::UpdateRoles,
            self::DeleteRoles,
            self::ViewAnyRoles,
            self::RestoreRoles,
            self::ForceDeleteRoles => 'roles',

            self::CreateClients,
            self::ViewClients,
            self::UpdateClients,
            self::DeleteClients,
            self::ViewAnyClients,
            self::RestoreClients,
            self::ForceDeleteClients => 'clients',

            self::CreateTasks,
            self::ViewTasks,
            self::UpdateTasks,
            self::DeleteTasks,
            self::ViewAnyTasks,
            self::RestoreTasks,
            self::ForceDeleteTasks => 'tasks',
        };
    }
}
